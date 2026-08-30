<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Collections\DataBreachRecordCollection;
use App\Collections\SnapshotApprovalCollection;
use App\Collections\SnapshotCollection;
use App\Config\Feature;
use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Facades\Authorization;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\Resource;
use App\Filament\Resources\WpgProcessingRecordResource;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\SnapshotApproval;
use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\NoBreach;
use App\Models\States\Snapshot\Approved;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function __;
use function array_slice;
use function usort;

/**
 * Counts the work waiting for a user in one organisation.
 *
 * Everything here is scoped explicitly to the organisation passed in. The models
 * carry no tenant global scope — Filament scopes its resources through the
 * organisation relationship instead — so a query written against a model class
 * directly would silently count every organisation's rows.
 *
 * Date categories are split into overdue and upcoming. The two are disjoint (a
 * date is either before today or from today onwards), so a caller may add them
 * for a combined total without double-counting.
 */
readonly class AttentionCountService
{
    public function __construct(
        private DateWindow $dateWindow = new DateWindow(),
    ) {
    }

    /**
     * Processing records whose periodic review date has passed.
     */
    public function reviewsOverdue(Organisation $organisation): int
    {
        return $this->countAcrossReviewables(
            $organisation,
            fn (Builder $query): Builder => $this->dateWindow->overdue($query, 'review_at'),
        );
    }

    /**
     * Processing records due for review between today and the horizon.
     */
    public function reviewsSoon(Organisation $organisation): int
    {
        return $this->countAcrossReviewables(
            $organisation,
            fn (Builder $query): Builder => $this->dateWindow->soon($query, 'review_at'),
        );
    }

    public function documentsExpired(Organisation $organisation): int
    {
        return $this->dateWindow->overdue($organisation->documents()->getQuery(), 'expires_at')->count();
    }

    public function documentsExpiringSoon(Organisation $organisation): int
    {
        return $this->dateWindow->soon($organisation->documents()->getQuery(), 'expires_at')->count();
    }

    /**
     * Approvals still awaiting this user's signature in this organisation.
     *
     * Scoped through the snapshot's organisation rather than the assignment,
     * because an approval row itself has no organisation; a mandate holder
     * active in several organisations would otherwise see one combined number.
     */
    public function unsignedApprovals(Organisation $organisation, User $user): int
    {
        return SnapshotApproval::unsigned()
            ->assignedTo($user)
            ->whereSnapshotOrganisation($organisation)
            ->count();
    }

    /**
     * Data breaches still being handled. completed_at is the only field marking
     * the handling as finished.
     */
    public function openDataBreaches(Organisation $organisation): int
    {
        return $organisation->dataBreachRecords()
            ->whereNull('completed_at')
            ->count();
    }

    /**
     * Data breaches whose handling is not finished, longest open first.
     *
     * "Finished" comes from the state machine: closed, or assessed as not being
     * a breach at all. Not from ap_reported — whether a report to the Autoriteit
     * Persoonsgegevens was required depends on the risk to those involved, and a
     * breach correctly judged not notifiable is done, not overdue.
     *
     * completed_at is checked as well because it predates the state machine and
     * is still filled in by hand, so older records can be finished without ever
     * having left the default state.
     *
     * Breaches without a discovery date sort last: they need attention, but a
     * handful of them must never push every dated breach off a capped list.
     */
    public function openDataBreachRecords(Organisation $organisation, int $limit): DataBreachRecordCollection
    {
        $dataBreachRecords = $organisation->dataBreachRecords()
            ->whereNull('completed_at')
            ->whereNotIn('state', [Closed::$name, NoBreach::$name])
            ->orderByRaw('discovered_at is null')
            ->orderBy('discovered_at')
            ->limit($limit)
            ->get();

        Assert::isInstanceOf($dataBreachRecords, DataBreachRecordCollection::class);

        return $dataBreachRecords;
    }

    /**
     * The snapshots waiting for this user's signature, oldest request first.
     */
    public function unsignedApprovalsFor(Organisation $organisation, User $user, int $limit): SnapshotApprovalCollection
    {
        $snapshotApprovals = SnapshotApproval::unsigned()
            ->assignedTo($user)
            ->whereSnapshotOrganisation($organisation)
            ->with('snapshot')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        Assert::isInstanceOf($snapshotApprovals, SnapshotApprovalCollection::class);

        return $snapshotApprovals;
    }

    /**
     * Snapshots that have been through approval and are now waiting to be
     * established, longest waiting first.
     *
     * "Waiting" is the combination of two things, because neither alone means
     * it: the snapshot sits in the approved state — it was submitted for
     * approval, which is what notified the mandate holders — and every approval
     * on it has been signed. A snapshot still missing a signature is the mandate
     * holders' work, not the privacy officer's, and appears on their own list.
     *
     * Signed covers declined as well as approved. A declined signature is an
     * answer, and the decision about what to do with it is the privacy
     * officer's to make; leaving those rows off would hide a snapshot that no
     * one is going to act on otherwise.
     *
     * Snapshots carrying no approvals at all are excluded. They cannot have been
     * signed, so counting their empty approval list as "all signed" would put a
     * snapshot on this list the moment it was submitted, before anyone had
     * looked at it.
     */
    public function snapshotsAwaitingEstablishment(Organisation $organisation, int $limit): SnapshotCollection
    {
        $snapshots = $organisation->snapshots()
            ->getQuery()
            ->whereState('state', Approved::class)
            ->whereHas('snapshotApprovals')
            ->whereDoesntHave('snapshotApprovals', $this->unsignedApproval(...))
            ->reorder()
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        Assert::isInstanceOf($snapshots, SnapshotCollection::class);

        return $snapshots;
    }

    /**
     * Constrains an approval subquery to the signatures still outstanding.
     *
     * A named method rather than a closure inline: the relation resolves to the
     * approval model's own builder, so a closure typed against that builder does
     * not match the generic signature whereDoesntHave() declares.
     *
     * @param Builder<SnapshotApproval> $query
     */
    private function unsignedApproval(Builder $query): void
    {
        $query->whereIn('status', SnapshotApprovalStatus::unsigned());
    }

    /**
     * The register items and documents whose date has passed, most overdue
     * first, across every register that carries one.
     *
     * Merged into a single list rather than one per register because the
     * question it answers — what do I fix first — does not care which register
     * an item lives in.
     *
     * @return array<int, OverdueItem>
     */
    public function overdueItems(Organisation $organisation, int $limit): array
    {
        $items = [];

        foreach ($this->reviewableRegisters($organisation) as [$filamentResource, $type, $query]) {
            foreach ($this->dateWindow->overdue($query, 'review_at')->get() as $record) {
                $items[] = OverdueItem::forReview($record, $filamentResource, $type);
            }
        }

        // Documents carry their own view permission. Every role that may see
        // core entities happens to hold it too, but that is configuration
        // rather than a guarantee, so the two are checked separately here.
        if (Authorization::hasPermission(Permission::DOCUMENT_VIEW)) {
            $documents = $this->dateWindow->overdue($organisation->documents()->getQuery(), 'expires_at')->get();

            foreach ($documents as $document) {
                Assert::isInstanceOf($document, Document::class);

                $items[] = OverdueItem::forDocument($document);
            }
        }

        usort($items, static function (OverdueItem $a, OverdueItem $b): int {
            return $a->date <=> $b->date;
        });

        return array_slice($items, 0, $limit);
    }

    /**
     * Sums a review-date constraint over the three registers that carry one.
     * Algorithm and data breach records are deliberately absent: neither has a
     * review_at column.
     *
     * @param callable(Builder<covariant Model>): Builder<covariant Model> $constrain
     */
    private function countAcrossReviewables(Organisation $organisation, callable $constrain): int
    {
        $total = 0;

        foreach ($this->reviewableRegisters($organisation) as [, , $query]) {
            $total += $constrain($query)->count();
        }

        return $total;
    }

    /**
     * The registers carrying a periodic review date: the Filament resource that
     * owns each one so a caller can link back to it, a short label for listing
     * the record among other types, and the tenant-scoped query.
     *
     * WPG is left out when its feature flag is off: the dashboard would
     * otherwise count and link to a register the user cannot open.
     *
     * @return array<int, array{class-string<Resource>, string, Builder<covariant Model>}>
     */
    private function reviewableRegisters(Organisation $organisation): array
    {
        $registers = [
            [
                AvgResponsibleProcessingRecordResource::class,
                __('dashboard.type.avg_responsible'),
                $organisation->avgResponsibleProcessingRecords()->getQuery(),
            ],
            [
                AvgProcessorProcessingRecordResource::class,
                __('dashboard.type.avg_processor'),
                $organisation->avgProcessorProcessingRecords()->getQuery(),
            ],
        ];

        if (Feature::wpgEnabled()) {
            $registers[] = [
                WpgProcessingRecordResource::class,
                __('dashboard.type.wpg'),
                $organisation->wpgProcessingRecords()->getQuery(),
            ];
        }

        return $registers;
    }
}
