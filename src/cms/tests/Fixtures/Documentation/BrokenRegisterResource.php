<?php

declare(strict_types=1);

namespace Tests\Fixtures\Documentation;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use RuntimeException;

use function __;

/**
 * A register whose form cannot be assembled.
 *
 * Stands in for a real resource that bails out halfway - a component that needs
 * state the generator cannot provide, for instance. The generator must report
 * that and stop, rather than quietly leave a chapter out of the document.
 */
class BrokenRegisterResource extends Resource
{
    protected static ?string $model = AvgResponsibleProcessingRecord::class;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Schema $schema): Schema
    {
        throw new RuntimeException('this form cannot be assembled');
    }
}
