<?php

declare(strict_types=1);

namespace App\Filament\Resources\SnapshotResource\Pages;

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotDataSection;
use App\Facades\Authorization;
use App\Facades\DateFormat;
use App\Filament\Resources\SnapshotResource;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\ValueObjects\Markdown;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Jfcherng\Diff\DiffHelper;
use Livewire\Attributes\Url;
use Webmozart\Assert\Assert;

use function __;
use function abort_unless;
use function sprintf;

class CompareSnapshots extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SnapshotResource::class;
    protected static string $view = 'filament.resources.snapshot-resource.pages.compare-snapshots';

    #[Url]
    public ?string $fromId = null;

    #[Url]
    public ?string $toId = null;

    /**
     * Request-scoped cache: getSnapshots() is read several times per render
     * (title, options, diffs). Livewire re-instantiates the component per
     * request, so caching here stays correct while avoiding repeat queries.
     *
     * @var Collection<string, Snapshot>|null
     */
    private ?Collection $snapshots = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Page (unlike ViewRecord) does not authorize access on mount, so the
        // SNAPSHOT_VIEW policy would never run. Enforce the same check
        // ViewSnapshot gets automatically before rendering any diff.
        $this->authorizeAccess();

        abort_unless($this->getSnapshot()->snapshotSource !== null, 404);

        $snapshots = $this->getSnapshots();
        abort_unless($snapshots->count() >= 2, 404);

        if ($this->fromId === null || !$snapshots->has($this->fromId)) {
            // Default the "from" side to the version right before the anchor,
            // falling back to the oldest snapshot.
            $this->fromId = $this->defaultFromId($snapshots);
        }

        if ($this->toId === null || !$snapshots->has($this->toId)) {
            $this->toId = $this->getSnapshot()->id->toString();
        }
    }

    protected function authorizeAccess(): void
    {
        // Mirror SnapshotPolicy::view(), which ViewSnapshot enforces via
        // ViewRecord::mount() but a plain Page does not.
        abort_unless(Authorization::hasPermission(Permission::SNAPSHOT_VIEW), 403);
    }

    public function getTitle(): string|Htmlable
    {
        // The source is guaranteed non-null: mount() aborts otherwise.
        return __('snapshot.compare_title', [
            'name' => $this->getSource()->getDisplayName(),
        ]);
    }

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            SnapshotResource::getUrl('view', ['record' => $this->getSnapshot()]) => __('snapshot.model_singular'),
        ];
        $breadcrumbs[] = __('snapshot.compare');

        return $breadcrumbs;
    }

    /**
     * Snapshots of the same source, keyed by id, newest version first.
     *
     * `version` is a unique, monotonically increasing integer per source, so
     * "newest" here means the highest version number. The picker labels show
     * `created_at`; the two only diverge if snapshots are ever persisted out of
     * chronological version order (e.g. a cross-organisation import).
     *
     * @return Collection<string, Snapshot>
     */
    public function getSnapshots(): Collection
    {
        return $this->snapshots ??= $this->getSource()->snapshots()
            ->with('snapshotData')
            ->get()
            ->sortByDesc('version')
            ->keyBy(static fn (Snapshot $snapshot): string => $snapshot->id->toString());
    }

    /**
     * Options for the version pickers: id => human label.
     *
     * @return array<string, string>
     */
    public function getVersionOptions(): array
    {
        return $this->getSnapshots()
            ->map(static function (Snapshot $snapshot): string {
                return sprintf(
                    '%s %d — %s',
                    __('snapshot.version'),
                    $snapshot->version,
                    DateFormat::toDateTime($snapshot->created_at),
                );
            })
            ->all();
    }

    /**
     * Side-by-side HTML diffs for each snapshot-data section.
     *
     * @return array<string, HtmlString>
     */
    public function getDiffs(): array
    {
        $snapshots = $this->getSnapshots();

        $from = $this->fromId !== null ? $snapshots->get($this->fromId) : null;
        $to = $this->toId !== null ? $snapshots->get($this->toId) : null;

        if ($from === null || $to === null) {
            return [];
        }

        return [
            SnapshotDataSection::PUBLIC->value => $this->diffSection(
                $from->snapshotData?->public_markdown,
                $to->snapshotData?->public_markdown,
            ),
            SnapshotDataSection::PRIVATE->value => $this->diffSection(
                $from->snapshotData?->private_markdown,
                $to->snapshotData?->private_markdown,
            ),
        ];
    }

    private function diffSection(?Markdown $from, ?Markdown $to): HtmlString
    {
        // We diff the stored snapshot markdown rather than the rendered output:
        // SnapshotDataMarkdownRenderer injects the currently-established related
        // snapshots at render time, so its output is not a stable function of the
        // version and would produce phantom differences.
        $html = DiffHelper::calculate(
            (string) $from?->toString(),
            (string) $to?->toString(),
            'SideBySide',
            [
                'context' => 3,
                'ignoreWhitespace' => false,
            ],
            [
                'detailLevel' => 'word',
                'lineNumbers' => false,
                'showHeader' => false,
                'separateBlock' => true,
                'resultForIdenticals' => sprintf(
                    '<div class="snapshot-diff-empty">%s</div>',
                    __('snapshot.compare_no_changes'),
                ),
            ],
        );

        return new HtmlString($html);
    }

    private function getSnapshot(): Snapshot
    {
        $snapshot = $this->getRecord();
        Assert::isInstanceOf($snapshot, Snapshot::class);

        return $snapshot;
    }

    private function getSource(): SnapshotSource
    {
        $source = $this->getSnapshot()->snapshotSource;
        Assert::isInstanceOf($source, SnapshotSource::class);

        return $source;
    }

    /**
     * @param Collection<string, Snapshot> $snapshots
     */
    private function defaultFromId(Collection $snapshots): string
    {
        $version = $this->getSnapshot()->version;

        $previous = $snapshots
            ->first(static fn (Snapshot $snapshot): bool => $snapshot->version < $version);

        $fallback = $snapshots->last();
        Assert::isInstanceOf($fallback, Snapshot::class);

        return ($previous ?? $fallback)->id->toString();
    }
}
