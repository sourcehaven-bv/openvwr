<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Collections\SnapshotCollection;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Resources\OrganisationSnapshotApprovalResource;
use App\Services\Dashboard\AttentionCountService;
use Filament\Widgets\Widget;

use function app;

/**
 * Snapshots that have collected every signature and are now waiting to be
 * established.
 *
 * This is the privacy officer's half of the approval round trip. Submitting a
 * version for approval notifies the mandate holders, but nothing notified the
 * officer once they had all signed, so the last step — establishing the version
 * — was only found by opening the approval overview and reading the state of
 * each row by hand.
 *
 * Gated on the permission to establish rather than on a role: it lists work only
 * someone who may perform that transition can finish, and both the privacy
 * officer and the chief privacy officer hold it.
 *
 * Hides itself when nothing is waiting.
 */
class AwaitingEstablishmentWidget extends Widget
{
    public const int LIMIT = 8;

    // Directly after the personal signing list: both are approval work, and this
    // one is only ever actionable by a different person than that list is.
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.awaiting-establishment';

    /** @var array<string, SnapshotCollection> */
    private static array $snapshots = [];

    public static function canView(): bool
    {
        if (!Authorization::hasPermission(Permission::SNAPSHOT_STATE_TO_ESTABLISHED)) {
            return false;
        }

        return self::snapshots()->isNotEmpty();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getRows(): array
    {
        $rows = [];

        foreach (self::snapshots() as $snapshot) {
            $rows[] = [
                'name' => $snapshot->name,
                'waiting' => $snapshot->updated_at->diffForHumans(),
                'url' => OrganisationSnapshotApprovalResource::getUrl('view', ['record' => $snapshot]),
            ];
        }

        return $rows;
    }

    public function getAllUrl(): string
    {
        return OrganisationSnapshotApprovalResource::getUrl();
    }

    public function hasMore(): bool
    {
        return self::snapshots()->count() >= self::LIMIT;
    }

    /**
     * Resolved once per request: canView() and the view both need the rows, and
     * Filament calls them separately.
     *
     * Keyed by organisation only. Unlike the personal signing list these rows are
     * the same for everyone who may establish, so there is no second user whose
     * view of them differs.
     */
    private static function snapshots(): SnapshotCollection
    {
        $organisation = Authentication::organisation();
        $cacheKey = $organisation->id->toString();

        if (!isset(self::$snapshots[$cacheKey])) {
            self::$snapshots[$cacheKey] = app(AttentionCountService::class)
                ->snapshotsAwaitingEstablishment($organisation, self::LIMIT);
        }

        return self::$snapshots[$cacheKey];
    }
}
