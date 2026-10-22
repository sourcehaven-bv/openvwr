<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Models\Organisation;
use App\Services\CrossOrgAuthorization;
use App\Transfer\CrossOrgCopier;
use App\Transfer\Export\BundleBuilder;
use App\Transfer\Export\RelatedItemCollector;
use App\Transfer\Import\PreviewBuilder;
use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use App\Transfer\TransferException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

use function __;
use function abort_if;
use function abort_unless;
use function app;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function collect;
use function explode;
use function is_array;
use function is_string;
use function request;

/**
 * Copies selected register content from the current organisation directly into another
 * organisation the user administers, without an intermediate zip. Reached from the record
 * tables via the "copy to organisation" bulk action, which passes the selected ids.
 *
 * Every id and organisation coming from the client is re-scoped and re-authorized here and
 * again in CrossOrgCopier; the page never trusts the request for access decisions.
 */
class TransferCopy extends Page
{
    protected static ?string $slug = 'transfer-copy';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.transfer-copy';

    /**
     * The selected record type and ids arrive as query-string parameters from the bulk
     * action. Bound with #[Url] so they hydrate from the query string in the real request
     * (Filament does not map query params to mount arguments); mount() re-scopes and
     * re-authorizes them into the locked recordType/recordIds below.
     */
    #[Url]
    public ?string $type = null;

    #[Url]
    public ?string $records = null;

    /** @var list<string> */
    #[Locked]
    public array $recordIds = [];

    #[Locked]
    public ?string $recordType = null;

    public ?string $targetOrganisationId = null;

    /**
     * Selected related ids keyed by relation name. Bound to Livewire and therefore
     * client-controlled, so it is treated as untyped input and validated in selectedRelated().
     *
     * @var array<mixed>
     */
    public array $related = [];

    /** @var array<string, array<string, mixed>> */
    public array $items = [];

    #[Locked]
    public bool $analysed = false;

    public function mount(): void
    {
        abort_unless(Authorization::hasPermission(Permission::TRANSFER_EXPORT), 403);

        $recordType = TransferEntityType::tryFrom((string) $this->type);

        abort_if($recordType === null || !$recordType->isMainRecord(), 404);

        $this->recordType = $recordType->value;
        $ids = $this->records === null || $this->records === '' ? [] : explode(',', $this->records);
        $this->recordIds = $this->scopeRecordIds($recordType, $ids);

        abort_if($this->recordIds === [], 404);

        // Pre-select all related items so the user opts out rather than in.
        foreach ($this->relatedGroups() as $relationName => $group) {
            $this->related[$relationName] = array_map('strval', array_keys($group['options']));
        }
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public function getTitle(): string
    {
        return __('transfer.copy_page_title');
    }

    /**
     * Records the user actually selected, re-scoped to the current organisation so a
     * tampered id list cannot reach another organisation's rows.
     *
     * @param list<mixed> $ids
     *
     * @return list<string>
     */
    private function scopeRecordIds(TransferEntityType $type, array $ids): array
    {
        $ids = array_values(array_filter($ids, static fn (mixed $id): bool => is_string($id) && $id !== ''));

        if ($ids === []) {
            return [];
        }

        $scoped = $type->modelClass()::query()
            ->whereBelongsTo(Authentication::organisation())
            ->whereIn('id', $ids)
            ->get();

        $scopedIds = [];
        foreach ($scoped as $record) {
            $scopedIds[] = ModelGraph::id($record);
        }

        return $scopedIds;
    }

    /**
     * The organisations the user may copy into: the ones they belong to (other than the
     * current) where they hold the import permission.
     *
     * @return array<string, string>
     */
    public function targetOptions(): array
    {
        $targets = app(CrossOrgAuthorization::class)
            ->copyTargetsFor(Authentication::user(), Authentication::organisation());

        $options = [];
        foreach ($targets as $organisation) {
            $options[$organisation->id->toString()] = $organisation->name;
        }

        return $options;
    }

    /**
     * @return array<string, array{type: TransferEntityType, options: array<string, string>}>
     */
    public function relatedGroups(): array
    {
        return app(RelatedItemCollector::class)->collect($this->selectedRecords());
    }

    /**
     * @return Collection<int|string, Model>
     */
    private function selectedRecords(): Collection
    {
        $type = TransferEntityType::from((string) $this->recordType);

        /** @var Collection<int|string, Model> $records */
        $records = $type->modelClass()::query()
            ->whereBelongsTo(Authentication::organisation())
            ->whereIn('id', $this->recordIds)
            ->get();

        return $records;
    }

    public function analyse(BundleBuilder $bundleBuilder, PreviewBuilder $previewBuilder): void
    {
        $target = $this->resolveTarget();

        if ($target === null) {
            Notification::make()
                ->title(__('transfer.copy_pick_target'))
                ->warning()
                ->send();

            return;
        }

        $type = TransferEntityType::from((string) $this->recordType);
        $bundle = $bundleBuilder->build($type, $this->recordIds, $this->selectedRelated(), Authentication::organisation());

        $this->items = $previewBuilder->build($bundle, $target);
        $this->analysed = true;
    }

    public function copy(CrossOrgCopier $copier): void
    {
        $target = $this->resolveTarget();
        abort_if($target === null, 403);

        $type = TransferEntityType::from((string) $this->recordType);

        try {
            $result = $copier->copy(
                $type,
                $this->recordIds,
                $this->selectedRelated(),
                $this->plan(),
                Authentication::organisation(),
                $target,
                Authentication::user(),
            );
        } catch (TransferException $exception) {
            Notification::make()
                ->title(__('transfer.copy_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('transfer.copy_finished'))
            ->body(__('transfer.import_finished_body', [
                'created' => $result->created,
                'overwritten' => $result->overwritten,
                'skipped' => $result->skipped,
            ]))
            ->success()
            ->send();

        $this->redirect(request()->header('referer') ?? '/');
    }

    public function resetAnalysis(): void
    {
        $this->analysed = false;
        $this->items = [];
    }

    /**
     * Resolve and re-authorize the chosen target organisation. Returns null when nothing
     * valid is selected; the copier enforces the same check again on execute.
     */
    private function resolveTarget(): ?Organisation
    {
        if (!is_string($this->targetOrganisationId) || $this->targetOrganisationId === '') {
            return null;
        }

        $target = Organisation::query()->find($this->targetOrganisationId);

        if ($target === null) {
            return null;
        }

        $authorized = app(CrossOrgAuthorization::class)
            ->userHasPermissionInOrganisation(Authentication::user(), $target, Permission::CORE_ENTITY_IMPORT);

        return $authorized && !$target->id->equals(Authentication::organisation()->id) ? $target : null;
    }

    /**
     * @return array<string, list<string>>
     */
    private function selectedRelated(): array
    {
        $selected = [];

        foreach ($this->related as $relationName => $ids) {
            if (!is_string($relationName) || !is_array($ids)) {
                continue;
            }

            $selected[$relationName] = array_values(array_filter(
                $ids,
                static fn (mixed $id): bool => is_string($id) && $id !== '',
            ));
        }

        return $selected;
    }

    /**
     * @return array<string, array{selected: bool, strategy: ?string}>
     */
    private function plan(): array
    {
        $plan = [];

        foreach ($this->items as $id => $item) {
            $strategy = $item['strategy'] ?? null;

            $plan[$id] = [
                'selected' => (bool) ($item['selected'] ?? false),
                'strategy' => is_string($strategy) ? $strategy : null,
            ];
        }

        return $plan;
    }

    /**
     * @return Collection<string, Collection<string, array<string, mixed>>>
     */
    public function groupedItems(): Collection
    {
        return collect($this->items)->groupBy('type_label', true);
    }

    /**
     * Every analysed item already exists in the target and is unchanged, so there is nothing
     * to copy. Used to replace the copy button with a "nothing to do" notice.
     */
    public function allUnchanged(): bool
    {
        if ($this->items === []) {
            return false;
        }

        foreach ($this->items as $item) {
            if (($item['unchanged'] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }
}
