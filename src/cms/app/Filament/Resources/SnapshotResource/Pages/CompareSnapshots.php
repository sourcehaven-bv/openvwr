<?php

declare(strict_types=1);

namespace App\Filament\Resources\SnapshotResource\Pages;

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotDataSection;
use App\Facades\Authorization;
use App\Facades\DateFormat;
use App\Filament\Resources\SnapshotResource;
use App\Models\Contracts\SnapshotSource;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\ValueObjects\Markdown;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Jfcherng\Diff\DiffHelper;
use Livewire\Attributes\Url;
use Webmozart\Assert\Assert;

use function __;
use function abort_unless;
use function class_basename;
use function implode;
use function sprintf;

class CompareSnapshots extends Page
{
    use InteractsWithRecord;

    /**
     * Diff key for the related-entities section. Unlike the other sections it
     * has no SnapshotDataSection counterpart: it is derived from the snapshot's
     * relations rather than from a snapshot_data column.
     */
    private const string RELATED_SECTION = 'related_snapshot_sources';

    protected static string $resource = SnapshotResource::class;
    protected string $view = 'filament.resources.snapshot-resource.pages.compare-snapshots';

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
            ->with(['snapshotData', 'relatedSnapshotSources.snapshotSource'])
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
     * Side-by-side HTML diffs, keyed by section slug. The keys stay
     * locale-independent so callers and tests can address a section without
     * knowing its translation; the view resolves the heading.
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
            self::RELATED_SECTION => $this->diffText(
                $this->relatedSourcesText($from),
                $this->relatedSourcesText($to),
            ),
        ];
    }

    /**
     * Heading for a section key returned by getDiffs(). The related-entities
     * section is not backed by a snapshot_data column, so it does not follow
     * the '<section>_data' naming the other two share.
     */
    public function getDiffHeading(string $section): string
    {
        return $section === self::RELATED_SECTION
            ? __(sprintf('snapshot.%s', self::RELATED_SECTION))
            : __(sprintf('snapshot.%s_data', $section));
    }

    /**
     * The many-to-many links (systems, processors, receivers, ...) captured on
     * a snapshot live in `related_snapshot_sources`, not in the stored
     * markdown -- the markdown only holds an inert placeholder tag that is
     * identical across versions. Diffing the markdown alone therefore reports
     * "no changes" even when a whole system was added or removed, so we render
     * the captured links to a stable text block and diff that separately.
     *
     * Only the entity's identity is used. The renderer resolves each link to
     * its currently-established snapshot at render time, which is not a
     * function of this version and would produce phantom differences.
     */
    private function relatedSourcesText(Snapshot $snapshot): string
    {
        $entries = $snapshot->relatedSnapshotSources
            ->toBase()
            /** @return array{type: string, name: string, sort: string}|null */
            ->map(static function (RelatedSnapshotSource $related): ?array {
                // The morph target has no foreign key (uuidMorphs indexes only),
                // so a hard-deleted source leaves an orphan row resolving to
                // null. Skip it rather than take the whole compare page down.
                $source = $related->snapshotSource;

                if ($source === null) {
                    return null;
                }

                $name = $source->getDisplayName();

                return [
                    'type' => __(sprintf(
                        '%s.model_singular',
                        Str::snake(class_basename($related->snapshot_source_type)),
                    )),
                    'name' => $name,
                    // Case-insensitive sort key: capitalisation drift between
                    // versions must not reorder lines into a phantom diff.
                    'sort' => Str::lower($name),
                ];
            })
            ->filter();

        // Sort on both levels so a merely reordered capture is not reported as
        // a change: only genuine additions and removals should surface.
        $lines = [];
        $currentType = null;

        foreach ($entries->sortBy(['type', 'sort'])->all() as $entry) {
            if ($entry['type'] !== $currentType) {
                $currentType = $entry['type'];
                $lines[] = $currentType . ':';
            }

            $lines[] = sprintf('  - %s', $entry['name']);
        }

        return implode("\n", $lines);
    }

    private function diffSection(?Markdown $from, ?Markdown $to): HtmlString
    {
        // We diff the stored snapshot markdown rather than the rendered output:
        // SnapshotDataMarkdownRenderer injects the currently-established related
        // snapshots at render time, so its output is not a stable function of the
        // version and would produce phantom differences.
        return $this->diffText((string) $from?->toString(), (string) $to?->toString());
    }

    private function diffText(string $from, string $to): HtmlString
    {
        $html = DiffHelper::calculate(
            $from,
            $to,
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
