<?php

declare(strict_types=1);

namespace App\Models\States\Transitions;

use App\Enums\Notification\NotificationStream;
use App\Mail\SnapshotApproval\ApprovalRequest;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\InReview;
use App\Services\Notification\NotificationRecipientService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Webmozart\Assert\Assert;

class InReviewTransition extends StateTransition
{
    public function handle(): Snapshot
    {
        $this->transitionToState(InReview::class);
        $this->notifyReviewers();

        return $this->snapshot;
    }

    /**
     * Announces the version to the reviewers.
     *
     * This used to fire when a version was created, because creating one *was* the act of
     * submitting it. Versions are now created by simply saving, so the announcement moved
     * to the moment that still means "please look at this": sending the concept to review.
     */
    private function notifyReviewers(): void
    {
        // A stored snapshot always has a source: both morph columns are NOT NULL.
        $snapshotSource = $this->snapshot->snapshotSource;
        Assert::isInstanceOf($snapshotSource, SnapshotSource::class);

        /** @var NotificationRecipientService $notificationRecipientService */
        $notificationRecipientService = App::get(NotificationRecipientService::class);

        $users = $notificationRecipientService->getRecipients(
            NotificationStream::SNAPSHOT_CREATED,
            $snapshotSource->getOrganisation(),
        );

        if ($users->isEmpty()) {
            return;
        }

        Mail::to($users)->queue(new ApprovalRequest($this->snapshot));
    }
}
