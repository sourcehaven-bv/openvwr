<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\StaticWebsite\BuildEvent;
use App\Events\User\UserCreatedEvent;
use App\Listeners\PostMediaUploadHandler;
use App\Listeners\StaticWebsite\BuildHandler;
use App\Listeners\User\Import\UserCreatedHandler;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\OrganisationUserRole;
use App\Models\PublicWebsite;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\Stakeholder;
use App\Models\Tag;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use App\Observers\AvgProcessorProcessingRecordObserver;
use App\Observers\AvgResponsibleProcessingRecordObserver;
use App\Observers\DpiaPrescanRecordObserver;
use App\Observers\MediaObserver;
use App\Observers\OrganisationObserver;
use App\Observers\OrganisationUserObserver;
use App\Observers\OrganisationUserRoleObserver;
use App\Observers\PublicWebsiteObserver;
use App\Observers\PublicWebsiteTreeObserver;
use App\Observers\SnapshotApprovalObserver;
use App\Observers\SnapshotObserver;
use App\Observers\StakeholderObserver;
use App\Observers\TagObserver;
use App\Observers\UserAuditLogs;
use App\Observers\UserObserver;
use App\Observers\WpgProcessingRecordObserver;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // model
        MediaHasBeenAddedEvent::class => [
            PostMediaUploadHandler::class,
        ],
        UserCreatedEvent::class => [UserCreatedHandler::class],

        // static website
        BuildEvent::class => [BuildHandler::class],
    ];

    public function boot(): void
    {
        AvgProcessorProcessingRecord::observe(AvgProcessorProcessingRecordObserver::class);
        AvgResponsibleProcessingRecord::observe(AvgResponsibleProcessingRecordObserver::class);
        DpiaPrescanRecord::observe(DpiaPrescanRecordObserver::class);
        Media::observe(MediaObserver::class);
        Organisation::observe(OrganisationObserver::class);
        OrganisationUser::observe(OrganisationUserObserver::class);
        PublicWebsite::observe(PublicWebsiteObserver::class);
        PublicWebsiteTree::observe(PublicWebsiteTreeObserver::class);
        Snapshot::observe(SnapshotObserver::class);
        SnapshotApproval::observe(SnapshotApprovalObserver::class);
        Stakeholder::observe(StakeholderObserver::class);
        Tag::observe(TagObserver::class);
        User::observe([
            UserAuditLogs::class,
            UserObserver::class,
        ]);
        OrganisationUserRole::observe(OrganisationUserRoleObserver::class);
        WpgProcessingRecord::observe(WpgProcessingRecordObserver::class);
    }
}
