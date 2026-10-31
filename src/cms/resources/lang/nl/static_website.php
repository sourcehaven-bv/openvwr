<?php

declare(strict_types=1);

return [
    'content' => [
        'home_title' => 'OpenVWR',
        'privacy_statement' => 'Privacyverklaring',
    ],

    'public_from_section' => [
        'title' => 'Publieke beschikbaarheid',
        'subtitle' => 'Een verwerking staat op de publieke website als "Publiceer vanaf" is verstreken, er een vastgestelde versie is en de organisatie op de publieke website staat. Na elke publicatie controleren we of de pagina online staat.',
        'public_history' => 'Overzicht',
        'public_history_since' => 'Versie :version: publiek beschikbaar sinds :start',
        'public_history_from_to' => 'Versie :version: publiek beschikbaar geweest van :start tot :end',
    ],

    'publication_state' => [
        'published' => [
            'label' => 'Gepubliceerd',
            'explanation' => 'Versie :online_version staat sinds :date op de publieke website.',
        ],
        'updating' => [
            'label' => 'Wordt bijgewerkt',
            'explanation' => 'Versie :online_version staat online. Vastgestelde versie :established_version wordt nu gepubliceerd; dit duurt meestal enkele minuten.',
        ],
        'removing' => [
            'label' => 'Wordt verwijderd',
            'explanation' => 'Versie :online_version staat nog online, maar wordt bij de volgende publicatie verwijderd. :reason',
        ],
        'pending' => [
            'label' => 'Wordt gepubliceerd',
            'explanation' => 'De verwerking voldoet aan alle voorwaarden en wordt nu gepubliceerd. Dit duurt meestal enkele minuten.',
        ],
        'not_confirmed' => [
            'label' => 'Publicatie niet bevestigd',
            'explanation' => 'De verwerking voldoet aan alle voorwaarden, maar de controle van :date vond haar niet op de publieke website. Neem contact op met de beheerder als dit zo blijft.',
        ],
        'scheduled' => [
            'label' => 'Ingepland',
            'explanation' => 'De verwerking wordt op :date gepubliceerd.',
        ],
        'not_publishing_disabled' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'Publiceren is voor deze omgeving uitgeschakeld.',
        ],
        'not_public_from_missing' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'Vul "Publiceer vanaf" in om de verwerking te publiceren.',
        ],
        'not_no_established_version' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'Er is nog geen vastgestelde versie. Alleen vastgestelde versies worden gepubliceerd.',
        ],
        'not_no_public_content' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'De vastgestelde versie heeft geen publieke inhoud. Stel een nieuwe versie vast om de verwerking te publiceren.',
        ],
        'not_organisation_not_on_website' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'De organisatie staat niet op de publieke website. Een beheerder kan haar toevoegen onder "Website organogram".',
        ],
        'not_website_section_not_public' => [
            'label' => 'Niet gepubliceerd',
            'explanation' => 'Het onderdeel ":section" van de publieke website heeft geen "Publiceer vanaf". Een beheerder kan dat instellen onder "Website organogram".',
        ],
    ],

    'publication_version_in_progress' => 'Versie :version heeft de status ":state". Wijzigingen worden pas openbaar als die versie is vastgesteld.',
];
