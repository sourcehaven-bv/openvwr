<?php

declare(strict_types=1);

return [
    'model_singular' => 'Organisatie',
    'model_plural' => 'Organisaties',
    'table_empty_heading' => 'Geen organisaties',

    'section_general' => 'Algemeen',
    'section_prefix' => 'Prefix',
    'section_public' => 'Publieke website',

    'user_attach' => 'Koppel gebruikers',
    'user_attach_description' => 'Zoek de gebruiker door (een deel van) het mailadres op te geven. Reeds gekoppelde gebruikers worden niet opgenomen in de resultaten.',

    'allowed_email_domains' => 'Toegestane user e-mail domeinen',
    'allowed_email_domains_help' => 'Indien hier geen domeinen zijn toegevoegd, dan worden er geen restricties toegepast.',
    'allowed_email_domains_add' => 'Domein toevoegen',
    'avatar' => 'Avatar',
    'poster' => 'Poster',
    'slug' => 'URL-segment',
    'allowed_ips' => 'Toegestane IP-adressen',
    'review_at_default_in_months' => 'Standaard periode voor periodieke review (in maanden)',
    'public_website_content' => 'Tekst publieke website',

    'help_slug' => 'Bepaalt het webadres van het portaal; gebruik alleen kleine letters en koppeltekens.',
    'help_allowed_ips' => 'Eén IP-adres, reeks of CIDR-notatie per regel; geldt niet voor de inlogpagina zelf.',
    'help_review_at_default_in_months' => 'Wordt toegepast zodra een versie wordt vastgesteld en de reviewdatum nog leeg is.',
    'help_responsible_legal_entity' => 'De rechtspersoon waaronder deze organisatie valt.',
    'help_public_website_content' => 'Introductietekst boven de registers op de publieke website.',
    'help_poster' => 'Afbeelding bovenaan de publieke website; een liggende afbeelding werkt het beste.',
    'entity_number_prefix' => 'Prefix',
    'register_entity_number_prefix' => 'Verwerking prefix',
    'databreach_entity_number_prefix' => 'Datalek prefix',
    'entity_number_prefix_edit' => 'Prefix aanpassen',
    'entity_number_unique_validation_message' => 'Deze prefix is al (eerder) in gebruik genomen, mogelijk door een andere organisatie: deze is niet meer beschikbaar.',

    'public_from_hint_icon_text' => 'Let op: Indien u dit veld leeg laat zal de verwerking op geen enkel moment gepubliceerd worden naar de publieke website.',
    'section_ap' => 'Gegevens voor datalekmeldingen',
    'coc_number' => 'KvK-nummer',
    'fg_registration_number' => 'Registratienummer FG',
    'sector' => 'Sector',
    'help_coc_number' => 'Wordt overgenomen in de melding aan de Autoriteit Persoonsgegevens.',
    'help_fg_registration_number' => 'Het nummer waarmee de functionaris gegevensbescherming bij de AP is aangemeld.',
    'help_sector' => 'De sector waarin de organisatie actief is; de AP vraagt hiernaar bij een melding.',
];
