<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\LabelSwatch;
use App\Filament\Resources\TagResource\TagResourceForm;
use App\Filament\TenantScoped;
use App\Models\Tag;
use App\Rules\CurrentOrganisation;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Webmozart\Assert\Assert;

use function __;
use function array_merge;

class TagsInput extends Select
{
    public static function make(string $name = 'tags'): static
    {
        return parent::make($name)
            ->label(__('tag.model_plural'))
            ->hintIcon('heroicon-o-information-circle', __('tag.hint_icon_text'))
            ->multiple()
            ->relationship('tags', 'name', TenantScoped::getAsClosure())
            // The picker shows the same colour the label has everywhere else;
            // without this the chosen labels all render in the primary colour.
            //
            // getOptionLabelsUsing rather than getOptionLabelFromRecordUsing:
            // the latter keys its result by the record's key, and ids are cast
            // to a Uuid object here, which PHP cannot use as an array key.
            ->getOptionLabelsUsing(static fn (array $values): array => self::swatchesByKey($values))
            ->allowHtml()
            ->rules([CurrentOrganisation::forModel(Tag::class)])
            ->searchable(['name'])
            ->createOptionForm(self::createTagOptionsForm())
            ->createOptionUsing(static function (array $data): string {
                $tagData = array_merge($data, ['organisation_id' => Authentication::organisation()->id->toString()]);
                Assert::isMap($tagData);

                return Tag::create($tagData)->id->toString();
            });
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<string, string>
     */
    private static function swatchesByKey(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $labels = [];

        foreach (Tag::query()->whereKey($values)->orderBy('name')->get() as $tag) {
            $labels[$tag->id->toString()] = LabelSwatch::make($tag->color, $tag->name)->toHtml();
        }

        return $labels;
    }

    /**
     * @return array<Component>
     */
    private static function createTagOptionsForm(): array
    {
        $hasPermission = Authorization::hasPermission(Permission::TAG_CREATE);
        if (!$hasPermission) {
            return [];
        }

        return TagResourceForm::getSchema();
    }
}
