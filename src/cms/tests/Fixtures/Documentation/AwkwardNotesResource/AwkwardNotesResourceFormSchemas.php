<?php

declare(strict_types=1);

namespace Tests\Fixtures\Documentation\AwkwardNotesResource;

use App\Documentation\DocNote;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use RuntimeException;

/**
 * Schema methods carrying a #[DocNote] that cannot be resolved.
 *
 * Each one represents a way a note can fail to land in the document. None of
 * them may derail the generator: the section simply gets no explanation.
 */
class AwkwardNotesResourceFormSchemas
{
    /**
     * A note on a method that yields no field at all, so there is nothing to
     * anchor it to.
     *
     * @return array<Component>
     */
    #[DocNote('documentation.avg_responsible_processing_record.stakeholders')]
    public static function getWithoutFields(): array
    {
        return [Grid::make()];
    }

    /**
     * A note on a method that cannot be called without arguments.
     *
     * @return array<Component>
     */
    #[DocNote('documentation.avg_responsible_processing_record.stakeholders')]
    public static function getWithRequiredArgument(string $required): array
    {
        return [TextInput::make($required)];
    }

    /**
     * A note on a method that throws while being called.
     *
     * @return array<Component>
     */
    #[DocNote('documentation.avg_responsible_processing_record.stakeholders')]
    public static function getThatThrows(): array
    {
        throw new RuntimeException('cannot be assembled outside a request');
    }

    /**
     * A note on a method that does not return a list of components.
     */
    #[DocNote('documentation.avg_responsible_processing_record.stakeholders')]
    public static function getSomethingElse(): string
    {
        return 'not a schema';
    }

    /**
     * A note whose translation key does not exist, which would otherwise put a
     * raw identifier in the document.
     *
     * @return array<Component>
     */
    #[DocNote('documentation.a_key_that_does_not_exist')]
    public static function getWithMissingTranslation(): array
    {
        return [TextInput::make('untranslated')];
    }
}
