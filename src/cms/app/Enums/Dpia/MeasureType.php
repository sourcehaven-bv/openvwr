<?php

declare(strict_types=1);

namespace App\Enums\Dpia;

use function __;

/**
 * Paragraaf 17 asks for technische, organisatorische en juridische maatregelen.
 * Typing them makes it visible when a DPIA leans on only one kind.
 */
enum MeasureType: string
{
    case TECHNICAL = 'technisch';
    case ORGANISATIONAL = 'organisatorisch';
    case LEGAL = 'juridisch';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function label(): string
    {
        return __('dpia_record.measure_type_' . $this->value);
    }
}
