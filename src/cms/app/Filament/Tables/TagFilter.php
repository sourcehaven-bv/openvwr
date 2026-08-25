<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Filament\LabelSwatch;
use App\Filament\TenantScoped;
use App\Models\Tag;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

use function __;
use function count;
use function e;
use function implode;
use function is_array;

class TagFilter extends SelectFilter
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'tag')
            ->label(__('tag.model_plural'))
            ->relationship('tags', 'name', TenantScoped::getAsClosure())
            ->getOptionLabelsUsing(static fn (array $values): array => self::swatchesByKey($values))
            ->searchable()
            ->multiple()
            ->indicateUsing(static fn (array $state): array => self::indicators($state));
    }

    /**
     * The options carry their own swatch, so the filter reads the same way as
     * the badges in the table it filters. allowHtml is set here rather than on
     * the filter because SelectFilter builds its own field and does not pass
     * that setting through.
     */
    public function getFormField(): Select
    {
        return parent::getFormField()->allowHtml();
    }

    /**
     * The labels for the values currently selected.
     *
     * getOptionLabelsUsing rather than getOptionLabelFromRecordUsing: the
     * latter keys its result by the record's key, and this project casts ids
     * to a Uuid object, which PHP cannot use as an array key (see
     * Select::setUp, where the relationship branch builds that map). Building
     * the map here keeps the key a string.
     *
     * @param array<mixed> $values
     *
     * @return array<string, string>
     */
    private static function swatchesByKey(array $values): array
    {
        $labels = [];

        foreach (self::tags($values) as $tag) {
            $labels[$tag->id->toString()] = LabelSwatch::make($tag->color, $tag->name)->toHtml();
        }

        return $labels;
    }

    /**
     * The "Actieve filters" bar.
     *
     * Filament joins the selected labels into one comma-separated string in a
     * single indicator, which loses the colours. Rebuilt here so each label
     * keeps its own swatch, in one indicator so the filter stays removable as
     * a whole - the same behaviour as before, only legible.
     *
     * @param array<mixed> $state
     *
     * @return array<Indicator>
     */
    private static function indicators(array $state): array
    {
        $values = $state['values'] ?? null;

        if (!is_array($values)) {
            return [];
        }

        $swatches = [];

        foreach (self::tags($values) as $tag) {
            $swatches[] = LabelSwatch::make($tag->color, $tag->name)->toHtml();
        }

        if (count($swatches) === 0) {
            return [];
        }

        return [
            Indicator::make(new HtmlString(
                '<span class="fi-label-swatch-group">'
                . '<span>' . e(__('tag.model_plural')) . ':</span>'
                . implode('', $swatches)
                . '</span>',
            )),
        ];
    }

    /**
     * @param array<mixed> $values
     *
     * @return iterable<Tag>
     */
    private static function tags(array $values): iterable
    {
        if (count($values) === 0) {
            return [];
        }

        return Tag::query()
            ->whereKey($values)
            ->orderBy('name')
            ->get();
    }
}
