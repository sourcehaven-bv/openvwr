<?php

declare(strict_types=1);

return [
    'title' => 'Voorbereiding melding Autoriteit Persoonsgegevens',
    'action_label' => 'AP-meldformulier voorbereiden',
    'action_pdf_label' => 'Download als PDF',

    'intro' => 'Dit overzicht volgt de hoofdstukindeling en vraagnummers van het online meldformulier'
        . ' datalekken van de Autoriteit Persoonsgegevens, zodat u het formulier van boven naar beneden'
        . ' kunt invullen. De AP accepteert meldingen uitsluitend via het online meldformulier; dit'
        . ' document is een hulpmiddel bij het invullen daarvan en is zelf geen melding.',
    'portal_hint' => 'Meldformulier: https://datalekken.autoriteitpersoonsgegevens.nl',

    'summary_title' => 'Voordat u begint',
    'summary_missing' => 'Nog te verzamelen (:count)',
    'summary_missing_empty' => 'Alle vragen die dit register kan beantwoorden, zijn ingevuld.',
    'summary_confirm' => 'Te controleren overname uit gekoppelde inhoud (:count)',
    'summary_confirm_explanation' => 'Deze antwoorden zijn afgeleid uit de verwerkingen waaraan dit datalek'
        . ' gekoppeld is. Ze beschrijven die verwerking en niet per se dit datalek: controleer per antwoord'
        . ' of het werkelijk gelekt is voordat u het overneemt in het meldformulier.',

    'hint_prefix' => 'De gekoppelde verwerking noemt',
    'hint_explanation' => 'Leg in het datalek vast wat er werkelijk gelekt is.',

    'source_recorded' => 'Uit het datalekregister',
    'source_derived' => 'Overname uit gekoppelde inhoud - controleren',
    'source_missing' => 'Niet in het register - zelf invullen',
    'origin_data_protection_officials' => 'Functionarissen gegevensbescherming van de organisatie',
    'origin_prefix' => 'Bron',
    'not_recorded' => 'Nog invullen',

    'chapter' => [
        'introduction' => 'Introductie',
        'international' => 'Internationale aspecten',
        'controller' => 'De verwerkingsverantwoordelijke',
        'timeline' => 'Tijdlijn',
        'breach' => 'Gegevens over de inbreuk',
        'personal_data' => 'Welke persoonsgegevens',
        'affected_people' => 'Getroffen personen',
        'prior_measures' => 'Maatregelen vooraf',
        'consequences' => 'Gevolgen',
        'follow_up' => 'Vervolgacties',
    ],

    'question' => [
        'notification_kind' => 'Wat voor soort melding wilt u doen?',
        'legal_basis' => 'Op grond van welke wettelijke bepaling doet u deze melding?',
        'other_supervisors' => 'Heeft u de inbreuk gemeld bij toezichthouders op andere meldplichten?',
        'cross_border' => 'Heeft de inbreuk gevolgen voor personen in meerdere landen?',
        'reported_other_dpas' => 'Heeft uw organisatie de inbreuk gemeld bij andere privacytoezichthouders?',
        'organisation_name' => 'Naam van het bedrijf of de organisatie',
        'responsible' => 'Verwerkingsverantwoordelijke(n)',
        'address' => 'Adres, postcode en plaats',
        'fg_registration_number' => 'Registratienummer van de FG',
        'coc_number' => 'KvK-nummer',
        'sector' => 'In welke sector is de organisatie actief?',
        'reporter' => 'Wie meldt de inbreuk? (naam, functie, e-mailadres, telefoonnummer)',
        'contact_person' => 'Contactpersoon voor de AP, indien afwijkend van de melder',
        'other_organisations' => 'Waren er andere organisaties betrokken bij de inbreuk?',
        'started_at' => '(Mogelijke) startdatum van de inbreuk',
        'ended_at' => '(Mogelijke) einddatum van de inbreuk',
        'discovered_at' => 'Wanneer is het incident ontdekt?',
        'how_discovered' => 'Geef (kort) aan hoe u de inbreuk heeft ontdekt',
        'late_notification_reason' => 'Indien later dan 72 uur gemeld: waarom?',
        'nature_of_breach' => 'Aard van de inbreuk (vertrouwelijkheid, integriteit, beschikbaarheid)',
        'nature_of_incident' => 'Aard van het incident',
        'summary' => 'Beschrijving van het incident',
        'attachments' => 'Ondersteunende documentatie',
        'personal_data_categories' => 'Persoonsgegevens in het algemeen',
        'special_categories' => 'Bijzondere categorieën van persoonsgegevens',
        'record_count' => 'Hoeveel gegevensrecords zijn getroffen?',
        'affected_groups' => 'Welke groep(en) betrokkenen zijn getroffen?',
        'affected_description' => 'Nadere omschrijving van de groep(en) betrokkenen',
        'affected_count' => 'Aantal betrokkenen (exact, of minimum en maximum)',
        'encrypted_beforehand' => 'Waren de persoonsgegevens vooraf versleuteld, gehasht of anderszins ontoegankelijk?',
        'pseudonymisation_from_processing' => 'Pseudonimisering volgens de gekoppelde verwerking(en)',
        'consequences_controller' => '(Mogelijke) gevolgen voor de verwerkingsverantwoordelijke en de persoonsgegevens',
        'consequences_data_subjects' => '(Mogelijke) gevolgen voor de betrokkene(n)',
        'risk_severity' => 'Inschatting ernst: verwaarloosbaar, beperkt, aanzienlijk of zeer groot',
        'estimated_risk' => 'Toelichting op de risico-inschatting',
        'reported_to_involved' => 'Heeft u de inbreuk gemeld aan de betrokkene(n)?',
        'reported_to_involved_communication' => 'Op welke wijze zijn de betrokkenen geïnformeerd?',
        'reported_to_involved_count' => 'Aan hoeveel personen heeft u de inbreuk gemeld?',
        'measures' => 'Welke maatregelen heeft u getroffen?',
    ],
];
