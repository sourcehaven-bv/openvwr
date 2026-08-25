<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
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
            // Labels hold a variable number of chips and wrap onto several
            // lines; half a row squeezes them next to an unrelated field. Set
            // here rather than per form, so the field looks the same on every
            // entity that has it.
            ->columnSpanFull()
            ->multiple()
            ->relationship('tags', 'name', TenantScoped::getAsClosure())
            ->rules([CurrentOrganisation::forModel(Tag::class)])
            ->searchable(['name'])
            // Tags are few and reused often, so offer them straight away
            // (most recently edited first) rather than only on typing.
            ->preload()
            ->options(static function (): array {
                return self::groupedTagOptions();
            })
            ->createOptionForm(self::createTagOptionsForm())
            ->createOptionUsing(static function (array $data): string {
                $tagData = array_merge($data, ['organisation_id' => Authentication::organisation()->id->toString()]);
                Assert::isMap($tagData);

                return Tag::create($tagData)->id->toString();
            });
    }

    /**
     * The selectable tags: the most recently edited, under a heading that
     * explains the ordering (see {@see RecentFirstOptions}).
     *
     * @return array<string, array<string, string>>
     */
    public static function groupedTagOptions(): array
    {
        $query = Tag::query();
        (TenantScoped::getAsClosure())($query);

        $recent = $query
            ->orderByDesc('updated_at')
            ->limit(RecentFirstOptions::RECENT_COUNT)
            ->pluck('name', (new Tag())->getKeyName())
            ->all();

        return RecentFirstOptions::group(RecentFirstOptions::fromPlucked($recent));
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
