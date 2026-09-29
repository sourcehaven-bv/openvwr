<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Section;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

use function md5;
use function sprintf;

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

    /**
     * The name only has to be unique within the schema, and never reaches the
     * user: the label is hidden and the content carries the text. v4 rejects an
     * empty name, so it is derived from the content rather than invented, which
     * keeps two blocks in one section apart.
     */
    private static function makePlaceholderWithHtmlString(string $info): Placeholder
    {
        return Placeholder::make(sprintf('information_block_%s', md5($info)))
            ->hiddenLabel()
            ->content(self::makeHtmlString($info));
    }

    private static function makeHtmlString(string $input): HtmlString
    {
        return new HtmlString($input);
    }
}
