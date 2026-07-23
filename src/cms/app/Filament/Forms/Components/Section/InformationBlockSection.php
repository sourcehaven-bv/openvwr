<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Section;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;

class InformationBlockSection extends Section
{
    public static function makeCollapsible(string $heading, string $info, ?string $extraInfo = null): static
    {
        $informationBlockSection = parent::make($heading)
            // A real, consistent toggle that starts closed: a saved record
            // should open with its information blocks collapsed rather than
            // everything expanded at once.
            ->collapsible()
            ->collapsed();

        if ($extraInfo === null) {
            return $informationBlockSection
                ->schema([
                    self::makePlaceholderWithHtmlString($info),
                ]);
        }

        return $informationBlockSection
            ->description(self::makeHtmlString($info))
            ->schema([
                self::makePlaceholderWithHtmlString($extraInfo),
            ]);
    }

    private static function makePlaceholderWithHtmlString(string $info): Placeholder
    {
        return Placeholder::make('')
            ->hiddenLabel()
            ->content(self::makeHtmlString($info));
    }

    private static function makeHtmlString(string $input): HtmlString
    {
        return new HtmlString($input);
    }
}
