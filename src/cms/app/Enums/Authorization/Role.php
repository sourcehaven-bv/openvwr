<?php

declare(strict_types=1);

namespace App\Enums\Authorization;

enum Role: string
{
    case CHIEF_PRIVACY_OFFICER = 'chief-privacy-officer';
    case COUNSELOR = 'counselor';
    case DATA_PROTECTION_OFFICIAL = 'data-protection-official';
    case FUNCTIONAL_MANAGER = 'functional-manager';
    case INPUT_PROCESSOR = 'input-processor';
    case INPUT_PROCESSOR_DATABREACH = 'input-processor-databreach';
    case MANDATE_HOLDER = 'mandate-holder';
    case PRIVACY_OFFICER = 'privacy-officer';

    /**
     * @return array<Role>
     */
    public static function globalRoles(): array
    {
        return [
            self::FUNCTIONAL_MANAGER,
        ];
    }

    /**
     * @return array<Role>
     */
    public static function organisationRoles(): array
    {
        return [
            self::CHIEF_PRIVACY_OFFICER,
            self::COUNSELOR,
            self::DATA_PROTECTION_OFFICIAL,
            self::INPUT_PROCESSOR,
            self::INPUT_PROCESSOR_DATABREACH,
            self::MANDATE_HOLDER,
            self::PRIVACY_OFFICER,
        ];
    }

    /**
     * @return array<array-key, array<Role>>
     */
    public static function organisationRoleGroups(bool $includeCpoRoles): array
    {
        $organisationRoleGroups = [];

        if ($includeCpoRoles) {
            $organisationRoleGroups[] = [
                self::CHIEF_PRIVACY_OFFICER,
                self::MANDATE_HOLDER,
            ];
        }

        $organisationRoleGroups[] = [
            self::INPUT_PROCESSOR,
            self::INPUT_PROCESSOR_DATABREACH,
            self::PRIVACY_OFFICER,
        ];

        $organisationRoleGroups[] = [
            self::COUNSELOR,
            self::DATA_PROTECTION_OFFICIAL,
        ];

        return $organisationRoleGroups;
    }
}
