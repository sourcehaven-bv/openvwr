<?php

declare(strict_types=1);

namespace App\Filament\Actions\Exports;

use App\Enums\YesNoUnknown;
use App\Services\DateFormatService;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn as FilamentExportColumn;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

use function is_bool;
use function strip_tags;

class ExportColumn extends FilamentExportColumn
{
    public function formatState(mixed $state): mixed
    {
        if (is_bool($state)) {
            return $this->formatBoolean($state);
        }

        if ($state instanceof CarbonInterface) {
            return $this->formatDate($state);
        }

        if ($state instanceof BackedEnum) {
            return $this->formatEnum($state);
        }

        return parent::formatState($state);
    }

    private function formatBoolean(bool $state): string
    {
        return $state ? YesNoUnknown::YES->value : YesNoUnknown::NO->value;
    }

    private function formatDate(CarbonInterface $state): string
    {
        return $state->format(DateFormatService::FORMAT_DATE_TIME);
    }

    private function formatEnum(BackedEnum $state): string
    {
        if ($state instanceof HasLabel) {
            $label = $state->getLabel();
            if ($label instanceof Htmlable) {
                return strip_tags($label->toHtml());
            }
            if ($label !== null) {
                return $label;
            }
        }

        return (string) $state->value;
    }
}
