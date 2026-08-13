<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Config\Config;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Services\DateFormatService;
use Carbon\CarbonImmutable;
use Filament\Actions\ExportAction as FilamentExportAction;
use Illuminate\Support\Str;

use function __;
use function sprintf;

class ExportAction extends FilamentExportAction
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label(__('general.export'))
            ->visible(Authorization::hasPermission(Permission::EXPORT))
            ->columnMapping(false)
            // Records which organisation is being exported, so the queued job can
            // restore the tenant and rebuild the same per-document-type columns it
            // wrote into the header. Resolved here because this closure still runs
            // in the request, where there is a tenant; the job has none.
            ->options(static fn (): array => [
                'organisation_id' => Authentication::organisation()->getKey()->toString(),
            ])
            ->fileName(static function (): string {
                return sprintf(
                    '%s-%s-export',
                    DateFormatService::toFilename(CarbonImmutable::now()),
                    Str::slug(Config::string('app.name')),
                );
            });
    }
}
