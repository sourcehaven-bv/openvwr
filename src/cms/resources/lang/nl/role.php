<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;

return [
    Role::CHIEF_PRIVACY_OFFICER->value => 'Chief privacy officer',
    Role::COUNSELOR->value => 'Raadpleger',
    Role::DATA_PROTECTION_OFFICIAL->value => 'Functionaris Gegevensbescherming',
    Role::FUNCTIONAL_MANAGER->value => 'Functioneel beheerder',
    Role::INPUT_PROCESSOR->value => 'Invoerder',
    Role::INPUT_PROCESSOR_DATABREACH->value => 'Invoerder Datalekken',
    Role::MANDATE_HOLDER->value => 'Mandaathouder',
    Role::PRIVACY_OFFICER->value => 'Privacy Officer',

    'descriptions' => [
        Role::CHIEF_PRIVACY_OFFICER->value => 'Volledig beheer van de registratie binnen de organisatie, inclusief het vaststellen van snapshots. Kan daarnaast alle organisatierollen toekennen.',
        Role::COUNSELOR->value => 'Alleen-lezen toegang tot de registratie, datalekken en snapshots.',
        Role::DATA_PROTECTION_OFFICIAL->value => 'Bekijkt de volledige registratie, plaatst FG-opmerkingen en kan gegevens exporteren. Wijzigt zelf niets.',
        Role::FUNCTIONAL_MANAGER->value => 'Beheert de applicatie: organisaties, gebruikers en globale instellingen. Kent rollen toe en heeft inzage in het beheerlogboek.',
        Role::INPUT_PROCESSOR->value => 'Legt verwerkingen, documenten en verantwoordelijken vast en werkt deze bij. Kan snapshots aanmaken maar niet vaststellen.',
        Role::INPUT_PROCESSOR_DATABREACH->value => 'Legt datalekken vast en werkt deze bij, met bijbehorende documenten en verantwoordelijken.',
        Role::MANDATE_HOLDER->value => 'Bekijkt de registratie en geeft een eigen akkoord op de aan hem of haar toegewezen snapshots.',
        Role::PRIVACY_OFFICER->value => 'Volledig beheer van de registratie binnen de organisatie, inclusief het vaststellen van snapshots. Kan rollen toekennen, behalve die van chief privacy officer.',
    ],
];
