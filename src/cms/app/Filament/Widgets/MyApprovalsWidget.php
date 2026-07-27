<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Collections\SnapshotApprovalCollection;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Resources\PersonalSnapshotApprovalResource;
use App\Services\Dashboard\AttentionCountService;
use Filament\Widgets\Widget;

use function app;

/**
 * Snapshots waiting for the signature of whoever is looking at the dashboard.
 *
 * For a mandate holder this is the whole job: they hold no register-editing
 * permissions, so it is the only list on the page they can act on.
 *
 * Hides itself when nothing is waiting.
 */
class MyApprovalsWidget extends Widget
{
    public const int LIMIT = 8;

    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.my-approvals';

    /** @var array<string, SnapshotApprovalCollection> */
    private static array $approvals = [];

    public static function canView(): bool
    {
        if (!Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL)) {
            return false;
        }

        return self::approvals()->isNotEmpty();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getRows(): array
    {
        $rows = [];

        foreach (self::approvals() as $snapshotApproval) {
            $snapshot = $snapshotApproval->snapshot;

            $rows[] = [
                'name' => $snapshot->name,
                'requested' => $snapshotApproval->created_at->diffForHumans(),
                'url' => PersonalSnapshotApprovalResource::getUrl('view', ['record' => $snapshot]),
            ];
        }

        return $rows;
    }

    public function getAllUrl(): string
    {
        return PersonalSnapshotApprovalResource::getUrl();
    }

    public function hasMore(): bool
    {
        return self::approvals()->count() >= self::LIMIT;
    }

    /**
     * Resolved once per request: canView() and the view both need the rows, and
     * Filament calls them separately.
     *
     * Keyed by user as well as organisation. These rows are personal — they are
     * the approvals assigned to whoever is looking — so an organisation-only key
     * would serve one user's worklist to the next user handled by the same PHP
     * process.
     */
    private static function approvals(): SnapshotApprovalCollection
    {
        $organisation = Authentication::organisation();
        $user = Authentication::user();
        $cacheKey = $organisation->id->toString() . ':' . $user->id->toString();

        if (!isset(self::$approvals[$cacheKey])) {
            self::$approvals[$cacheKey] = app(AttentionCountService::class)
                ->unsignedApprovalsFor($organisation, $user, self::LIMIT);
        }

        return self::$approvals[$cacheKey];
    }
}
