<?php

declare(strict_types=1);

namespace App\Enums;

use function __;
use function array_search;
use function is_array;
use function is_string;

/**
 * The six bases of article 6(1) AVG. A goal stores its basis as the label the
 * form showed, so this maps that label back to a stable value for the public
 * website, whichever language the form was in.
 */
enum AvgLegalBasis: string
{
    case CONSENT = 'consent';
    case CONTRACT = 'contract';
    case LEGAL_OBLIGATION = 'legal_obligation';
    case VITAL_INTERESTS = 'vital_interests';
    case PUBLIC_TASK = 'public_task';
    case LEGITIMATE_INTERESTS = 'legitimate_interests';

    public static function fromLabel(?string $label): ?self
    {
        if ($label === null) {
            return null;
        }

        foreach (['nl', 'en'] as $locale) {
            $options = __('avg_goal_legal_base.options', [], $locale);
            $index = is_array($options) ? array_search($label, $options, true) : false;
            if ($index !== false) {
                return self::cases()[$index];
            }
        }

        return null;
    }

    public function label(): string
    {
        $options = __('avg_goal_legal_base.options');
        $index = array_search($this, self::cases(), true);

        return is_array($options) && isset($options[$index]) && is_string($options[$index]) ? $options[$index] : $this->value;
    }
}
