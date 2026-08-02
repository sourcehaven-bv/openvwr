<?php

declare(strict_types=1);

namespace App\Enums\Dpia;

use App\Enums\StateColor;

use function __;

/**
 * The classification paragraaf 2 of the Rijksmodel asks for.
 *
 * Three of these are only allowed under an exception: verwerking of bijzondere
 * and strafrechtelijke persoonsgegevens is in principle forbidden (artikel 9
 * and 10 AVG), and a wettelijk identificatienummer may only be used where the
 * law says so. That is what paragraaf 12 is about, so the classification made
 * here decides which items paragraaf 12 has to justify.
 */
enum PersonalDataType: string
{
    case ORDINARY = 'gewoon';
    case SENSITIVE = 'gevoelig';
    case SPECIAL = 'bijzonder';
    case CRIMINAL = 'strafrechtelijk';
    case NATIONAL_IDENTIFIER = 'identificatienummer';

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
        return __('dpia_record.personal_data_type_' . $this->value);
    }

    /**
     * Whether verwerking of this type needs a ground in paragraaf 12.
     *
     * "Gevoelig" is not on this list: it is not a legal category but a signal
     * that the data is privacy-sensitive in practice, which raises the risk
     * assessment rather than the lawfulness test.
     */
    public function requiresExceptionGround(): bool
    {
        return match ($this) {
            self::SPECIAL, self::CRIMINAL, self::NATIONAL_IDENTIFIER => true,
            self::ORDINARY, self::SENSITIVE => false,
        };
    }

    public function color(): StateColor
    {
        return match ($this) {
            self::ORDINARY => StateColor::GRAY,
            self::SENSITIVE => StateColor::WARNING,
            self::SPECIAL, self::CRIMINAL, self::NATIONAL_IDENTIFIER => StateColor::DANGER,
        };
    }
}
