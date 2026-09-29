<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Resources\Resource as FilamentResource;
use Filament\Resources\ResourceConfiguration;
use Illuminate\Database\Eloquent\Model;

/**
 * v5 made the Filament resource generic in its model. Passing the parameters
 * through keeps a resource's own getEloquentQuery() typed to its own model
 * rather than collapsing to the base Model.
 *
 * @template TModel of Model = Model
 * @template TConfiguration of ResourceConfiguration = ResourceConfiguration
 *
 * @extends FilamentResource<TModel, TConfiguration>
 */
abstract class Resource extends FilamentResource
{
    protected static bool $hasNavigationBadge = false;

    public static function getNavigationBadge(): ?string
    {
        if (static::$hasNavigationBadge === true) {
            return (string) static::getEloquentQuery()->count();
        }

        return null;
    }
}
