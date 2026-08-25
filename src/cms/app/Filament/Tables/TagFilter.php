<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Filament\LabelSwatch;
use App\Filament\TenantScoped;
use App\Models\Tag;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;

use function __;
use function count;
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
     * single indicator, which loses the colours. One indicator per label
     * instead, each in its own colour.
     *
     * Removing a single label from here is not possible: Table::getFilterIndicators
     * overwrites every indicator's click handler with removeTableFilter(), and
     * that resets the whole field. Each cross therefore clears the label filter
     * as a whole, which is the behaviour Filament had before this change too.
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

        $indicators = [];

        foreach (self::tags($values) as $tag) {
            $indicators[] = Indicator::make($tag->name)
                ->color($tag->color->value ?? 'gray');
        }

        return $indicators;
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
