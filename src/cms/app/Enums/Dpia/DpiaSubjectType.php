<?php

declare(strict_types=1);

namespace App\Enums\Dpia;

use function __;

/**
 * Deel III of the Rijksmodel distinguishes a DPIA on regelgeving (wetten,
 * algemene maatregelen van bestuur, ministeriele regelingen) from a DPIA on
 * verwerkingen door de overheid. The expected level of detail and some of the
 * process steps differ, so the type is asked up front.
 */
enum DpiaSubjectType: string
{
    case PROCESSING = 'verwerking';
    case REGULATION = 'regelgeving';

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
        return __('dpia_record.subject_type_' . $this->value);
    }
}
