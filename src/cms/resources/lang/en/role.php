<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;

return [
    Role::CHIEF_PRIVACY_OFFICER->value => 'Chief privacy officer',
    Role::COUNSELOR->value => 'Viewer',
    Role::DATA_PROTECTION_OFFICIAL->value => 'Data Protection Officer',
    Role::FUNCTIONAL_MANAGER->value => 'Functional administrator',
    Role::INPUT_PROCESSOR->value => 'Contributor',
    Role::INPUT_PROCESSOR_DATABREACH->value => 'Contributor Personal Data Breaches',
    Role::MANDATE_HOLDER->value => 'Mandate holder',
    Role::PRIVACY_OFFICER->value => 'Privacy Officer',

    'descriptions' => [
        Role::CHIEF_PRIVACY_OFFICER->value => 'Full management of the record within the organisation, including establishing snapshots. Can also assign all organisation roles.',
        Role::COUNSELOR->value => 'Read-only access to the record, personal data breaches and snapshots.',
        Role::DATA_PROTECTION_OFFICIAL->value => 'Views the complete record, adds DPO remarks and can export data. Makes no changes.',
        Role::FUNCTIONAL_MANAGER->value => 'Manages the application: organisations, users and global settings. Assigns roles and has access to the administration log.',
        Role::INPUT_PROCESSOR->value => 'Records and updates processing activities, documents and controllers. Can create snapshots but not establish them.',
        Role::INPUT_PROCESSOR_DATABREACH->value => 'Records and updates personal data breaches, with associated documents and controllers.',
        Role::MANDATE_HOLDER->value => 'Views the record and gives their own approval on the snapshots assigned to them.',
        Role::PRIVACY_OFFICER->value => 'Full management of the record within the organisation, including establishing snapshots. Can assign roles, except that of chief privacy officer.',
    ],
];
