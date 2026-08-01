<?php

declare(strict_types=1);

// Labels en hulpteksten voor het DPIA-register.
//
// De hulpteksten zijn gebaseerd op deel III (toelichting) van het Model DPIA
// Rijksdienst v3.0. De nummering van de paragrafen is de officiele nummering
// uit deel II van dat model; die blijft ongewijzigd zodat de DPIA herleidbaar
// is voor FG's, auditors en de Autoriteit Persoonsgegevens.

return [
    'model_singular' => 'DPIA',
    'model_plural' => "DPIA's",
    'table_empty_heading' => "Geen DPIA's",
    'register_description' => 'Gegevensbeschermingseffectbeoordelingen volgens het Model DPIA Rijksdienst v3.0.',

    // Onderdelen A t/m D uit deel II van het model.
    'part_a' => 'A. Algemene kenmerken van de gegevensverwerkingen',
    'part_b' => 'B. Beoordeling rechtmatigheid',
    'part_c' => "C. Risico's voor betrokkenen",
    'part_d' => 'D. Maatregelen',
    'part_process' => 'Proces en verantwoording',

    // Stappen: de 17 paragrafen plus de procesonderdelen.
    'step_general' => 'Algemeen',
    'step_proposal' => '1. Voorstel',
    'step_personal_data' => '2. Persoonsgegevens',
    'step_processing' => '3. Gegevensverwerkingen',
    'step_techniques' => '4. Technieken en methoden',
    'step_purposes' => '5. Verwerkingsdoeleinden',
    'step_parties' => '6. Betrokken partijen',
    'step_interests' => '7. Belangen',
    'step_locations' => '8. Verwerkingslocaties',
    'step_legal_framework' => '9. Juridisch en beleidsmatig kader',
    'step_retention' => '10. Bewaartermijnen',
    'step_legal_basis' => '11. Rechtsgrond',
    'step_special_categories' => '12. Bijzondere persoonsgegevens',
    'step_purpose_limitation' => '13. Doelbinding',
    'step_necessity' => '14. Noodzaak en evenredigheid',
    'step_rights' => '15. Rechten van de betrokkenen',
    'step_risks' => "16. Risico's voor betrokkenen",
    'step_measures' => '17. Maatregelen',
    'step_consultation' => 'Consultatie en advies',
    'step_review' => 'Vaststelling en herziening',
    'step_relations' => 'Verwerkingen en systemen',
    'step_attachments' => 'Documenten en bijlagen',
    'step_remarks' => 'Opmerkingen',

    // Algemeen
    'name' => 'Naam van de DPIA',
    'help_name' => 'Gebruik een naam die herkenbaar is binnen de organisatie, bijvoorbeeld de naam van het project, de regeling of het systeem.',
    'subject_type' => 'Waar gaat deze DPIA over?',
    'help_subject_type' => 'Het model maakt onderscheid tussen een DPIA op regelgeving (wetten, algemene maatregelen van bestuur en ministeriële regelingen) en een DPIA op verwerkingen door of in opdracht van de overheid.',
    'subject_type_verwerking' => 'Verwerking (product, dienst, proces of systeem)',
    'subject_type_regelgeving' => 'Regelgeving (wet, AMvB of ministeriële regeling)',
    'prescan' => 'Bijbehorende pre-scan',
    'help_prescan' => 'De pre-scan waaruit blijkt dat deze DPIA nodig is. Zo blijft de aanleiding voor de DPIA traceerbaar.',

    // 1. Voorstel
    'proposal_description' => 'Beschrijving van het voorstel',
    'help_proposal_description' => 'Beschrijf op hoofdlijnen waar de DPIA op toeziet. Houd het begrijpelijk voor iemand die het project niet kent.',
    'proposal_motivation' => 'Totstandkoming en beweegredenen',
    'help_proposal_motivation' => 'Benoem hoe het voorstel tot stand is gekomen en wat de beweegredenen erachter zijn.',

    // 2. Persoonsgegevens
    'personal_data' => 'Persoonsgegevens',
    'personal_data_intro' => 'Beschrijf alle persoonsgegevens die worden verwerkt en classificeer ze. De classificatie bepaalt of in paragraaf 12 een uitzonderingsgrond nodig is.',
    'personal_data_item_label' => 'Persoonsgegeven',
    'add_personal_data' => 'Persoonsgegeven toevoegen',
    'personal_data_description_item' => 'Welk persoonsgegeven wordt verwerkt?',
    'help_personal_data_description_item' => 'Bijvoorbeeld "naam en adres", "burgerservicenummer" of "camerabeelden".',
    'personal_data_type' => 'Type persoonsgegeven',
    'help_personal_data_type' => 'Bijzondere en strafrechtelijke persoonsgegevens mogen in beginsel niet worden verwerkt, en een wettelijk identificatienummer alleen als de wet dat bepaalt. Bij die keuzes wordt hieronder om de uitzonderingsgrond gevraagd.',
    'personal_data_type_gewoon' => 'Gewoon',
    'personal_data_type_gevoelig' => 'Gevoelig',
    'personal_data_type_bijzonder' => 'Bijzonder (artikel 9 AVG)',
    'personal_data_type_strafrechtelijk' => 'Strafrechtelijk (artikel 10 AVG)',
    'personal_data_type_identificatienummer' => 'Wettelijk identificatienummer',
    'personal_data_subject_category' => 'Categorie betrokkenen',
    'help_personal_data_subject_category' => 'Van wie zijn deze gegevens? Bijvoorbeeld burgers, medewerkers of bezoekers.',
    'personal_data_source' => 'Bron',
    'help_personal_data_source' => 'Waar komt dit gegeven vandaan? Bijvoorbeeld de betrokkene zelf, een basisregistratie of een derde partij.',
    'personal_data_retention_period' => 'Bewaartermijn',
    'help_personal_data_retention_period' => 'Hoe lang wordt dit gegeven bewaard? De onderbouwing hoort bij paragraaf 10.',
    'personal_data_exception_ground' => 'Uitzonderingsgrond',
    'help_personal_data_exception_ground' => 'Op grond waarvan mag dit gegeven toch worden verwerkt? Verwijs naar de wettelijke uitzondering (artikel 9 of 10 AVG, of de Uitvoeringswet AVG) en onderbouw die.',
    'personal_data_exception_notice' => 'Dit type gegevens mag in beginsel niet worden verwerkt. Vul hieronder de uitzonderingsgrond in (paragraaf 12).',
    'personal_data_sources' => 'Aanvullende informatie over de persoonsgegevens',
    'help_personal_data_sources' => 'Optioneel tekstveld voor toelichting die niet bij een afzonderlijk gegeven hoort.',

    // 3. Gegevensverwerkingen
    'processing_description' => 'Beschrijving van de gegevensverwerkingen',
    'help_processing_description' => 'Geef alle gegevensverwerkingen weer en geef per verwerking aan welke categorieën persoonsgegevens daarin worden verwerkt. Een stroomschema mag als bijlage worden toegevoegd.',

    // 4. Technieken en methoden
    'techniques_description' => 'Wijze, middelen en methoden',
    'help_techniques_description' => 'Beschrijf op welke wijze en met welke (technische) middelen en methoden de persoonsgegevens worden verwerkt.',
    'automated_decision_making' => 'Er is sprake van (semi-)geautomatiseerde besluitvorming',
    'profiling' => 'Er is sprake van profilering',
    'cloud_processing' => 'Er wordt gebruikgemaakt van een cloudoplossing',
    'big_data_processing' => 'Er is sprake van big data-verwerkingen',
    'techniques_explanation' => 'Toelichting op de aangevinkte technieken',
    'help_techniques_explanation' => 'Beschrijf waaruit de aangevinkte technieken bestaan. Bij geautomatiseerde besluitvorming: beschrijf ook de onderliggende logica en de gevolgen voor de betrokkene.',

    // 5. Verwerkingsdoeleinden
    'purpose_description' => 'Doeleinden van de gegevensverwerkingen',
    'help_purpose_description' => 'Beschrijf de doeleinden van alle gegevensverwerkingen. Een doel moet welbepaald, uitdrukkelijk omschreven en gerechtvaardigd zijn.',

    // 6. Betrokken partijen
    'parties_description' => 'Betrokken partijen en hun rol',
    'help_parties_description' => 'Benoem alle betrokken partijen per gegevensverwerking en deel ze in onder de rollen: verwerkingsverantwoordelijke, gezamenlijke verwerkingsverantwoordelijke, verwerker, sub-verwerker, verstrekker, ontvanger, betrokkene en derde.',
    'parties_access' => 'Wie krijgt toegang tot welke gegevens?',
    'help_parties_access' => 'Benoem, wanneer bekend, welke functionarissen of afdelingen binnen deze partijen toegang krijgen tot welke categorieën persoonsgegevens.',

    // 7. Belangen
    'interests_description' => 'Belangen van de betrokken partijen',
    'help_interests_description' => 'Beschrijf alle belangen die de betrokken partijen hebben bij de gegevensverwerkingen.',
    'interests_data_subjects' => 'Mening van de betrokkenen',
    'help_interests_data_subjects' => 'Vraag betrokkenen of hun vertegenwoordigers naar hun mening over de verwerking indien relevant, en licht die mening hier toe.',

    // 8. Verwerkingslocaties
    'processing_locations' => 'In welke landen vinden de verwerkingen plaats?',
    'help_processing_locations' => 'Benoem de landen waar de gegevensverwerkingen plaatsvinden, inclusief de locaties van verwerkers en sub-verwerkers.',
    'outside_eea' => 'Er vinden verwerkingen plaats buiten de Europese Economische Ruimte',
    'transfer_mechanism' => 'Doorgiftemechanisme',
    'help_transfer_mechanism' => 'Beschrijf welk doorgiftemechanisme van toepassing is, bijvoorbeeld een adequaatheidsbesluit, standaardbepalingen inzake gegevensbescherming (SCC) of bindende bedrijfsvoorschriften.',
    'transfer_safeguards' => 'Aanvullende maatregelen bij doorgifte',
    'help_transfer_safeguards' => 'Noem of en welke aanvullende maatregelen van toepassing zijn. Overweeg ook of een DTIA nodig is.',

    // 9. Juridisch en beleidsmatig kader
    'legal_policy_framework' => 'Wet- en regelgeving en beleid',
    'help_legal_policy_framework' => 'Benoem alle wet- en regelgeving en beleid met mogelijke gevolgen voor de gegevensverwerkingen. De AVG en de Richtlijn hoeven niet genoemd te worden.',

    // 10. Bewaartermijnen
    'retention_periods' => 'Bewaartermijnen',
    'help_retention_periods' => 'Bepaal de bewaartermijnen aan de hand van de gegevensverwerkingen en de verwerkingsdoeleinden. Betrek hierbij ook de Archiefwet.',
    'retention_motivation' => 'Motivering van de bewaartermijnen',
    'help_retention_motivation' => 'Motiveer waarom deze bewaartermijnen niet langer zijn dan strikt noodzakelijk ten opzichte van de verwerkingsdoeleinden.',
    'retention_responsible' => 'Wie ziet toe op de bewaartermijn?',
    'help_retention_responsible' => 'Beschrijf wie toeziet op de bewaartermijn en op de vernietiging of archivering aan het einde daarvan.',

    // 11. Rechtsgrond
    'legal_basis' => 'Rechtsgronden',
    'help_legal_basis' => 'Bepaal op welke rechtsgronden de gegevensverwerkingen worden gebaseerd (artikel 6 AVG). Bij verwerkingen door de overheid zijn dat meestal een wettelijke verplichting of een taak van algemeen belang.',
    'legal_basis_conditions' => 'Hoe wordt aan de voorwaarden voldaan?',
    'help_legal_basis_conditions' => 'Iedere rechtsgrond stelt eigen voorwaarden. Licht per rechtsgrond toe hoe daaraan wordt voldaan.',

    // 12. Bijzondere persoonsgegevens
    'special_categories_no_personal_data' => 'Er zijn nog geen persoonsgegevens ingevuld bij paragraaf 2. Vul die eerst in; deze paragraaf volgt daaruit.',
    'special_categories_none' => 'Geen van de persoonsgegevens uit paragraaf 2 is geclassificeerd als bijzonder, strafrechtelijk of wettelijk identificatienummer. Er is dan geen uitzonderingsgrond nodig.',
    'special_categories_missing_ground' => 'Voor deze gegevens is nog geen uitzonderingsgrond ingevuld. Vul die in bij paragraaf 2, bij het gegeven zelf:',
    'special_categories_with_ground' => 'Voor deze gegevens is een uitzonderingsgrond vastgelegd in paragraaf 2:',
    'special_categories_additional' => 'Aanvullende toelichting op de uitzonderingsgronden',
    'help_special_categories_additional' => 'Optioneel. Gebruik dit veld voor een gezamenlijke onderbouwing of voor context die niet bij een afzonderlijk gegeven hoort.',
    'special_categories' => 'Er worden bijzondere of strafrechtelijke persoonsgegevens verwerkt',
    'help_special_categories' => 'Het verwerken van bijzondere of strafrechtelijke persoonsgegevens is in principe verboden. Verwerking is pas mogelijk wanneer een wettelijke uitzonderingsgrond van toepassing is.',
    'special_categories_exception' => 'Welke uitzonderingsgrond is van toepassing?',
    'help_special_categories_exception' => 'Beoordeel welke wettelijke uitzondering op het verwerkingsverbod van toepassing is (artikel 9 of 10 AVG, of de Uitvoeringswet AVG) en onderbouw dat.',
    'national_identification_number' => 'Er wordt een wettelijk identificatienummer verwerkt',
    'help_national_identification_number' => 'Bijvoorbeeld het burgerservicenummer. Gebruik daarvan is alleen toegestaan als de wet dat bepaalt.',
    'national_identification_number_basis' => 'Grondslag voor het identificatienummer',
    'help_national_identification_number_basis' => 'Beoordeel en onderbouw of het gebruik van het wettelijk identificatienummer is toegestaan.',

    // 13. Doelbinding
    'further_processing' => 'De gegevens worden ook voor een ander doel verwerkt dan waarvoor ze zijn verzameld',
    'help_further_processing' => 'Verdere verwerking voor een ander doeleinde is alleen toegestaan als daarvoor een wettelijke basis bestaat of als het nieuwe doel verenigbaar is met het oorspronkelijke.',
    'purpose_limitation' => 'Beoordeling van de doelbinding',
    'help_purpose_limitation' => 'Beoordeel of de verdere verwerking toelaatbaar is op grond van Unie- of lidstaatrechtelijk recht, dan wel verenigbaar is met het doel waarvoor de gegevens oorspronkelijk zijn verzameld.',

    // 14. Noodzaak en evenredigheid
    'necessity_proportionality' => 'Proportionaliteit',
    'help_necessity_proportionality' => 'Staat de inbreuk op de persoonlijke levenssfeer en de bescherming van de persoonsgegevens in evenredige verhouding tot de verwerkingsdoeleinden?',
    'necessity_subsidiarity' => 'Subsidiariteit',
    'help_necessity_subsidiarity' => 'Kunnen de verwerkingsdoeleinden in redelijkheid niet op een andere, voor de betrokkenen minder nadelige wijze worden verwezenlijkt?',

    // 15. Rechten van de betrokkenen
    'data_subject_rights_procedure' => 'Procedure voor de rechten van betrokkenen',
    'help_data_subject_rights_procedure' => 'Beschrijf hoe invulling wordt gegeven aan de rechten van betrokkenen: informatie, inzage, rectificatie, wissing, beperking, overdraagbaarheid, bezwaar en het recht niet onderworpen te worden aan geautomatiseerde besluitvorming.',
    'rights_restricted' => 'De rechten van betrokkenen worden beperkt',
    'rights_restriction_basis' => 'Grondslag voor de beperking',
    'help_rights_restriction_basis' => 'Beschrijf op grond van welke wettelijke uitzondering de beperking is toegestaan.',

    // 16. Risico's
    'risks' => "Risico's",
    'risks_intro' => "Beschrijf en beoordeel alle mogelijke risico's van de gegevensverwerkingen voor de rechten en vrijheden van betrokkenen. Denk niet alleen aan privacy, maar bijvoorbeeld ook aan het verbod op discriminatie.",
    'risk' => 'Risico',
    'risk_title' => 'Naam van het risico',
    'help_risk_title' => 'Een korte, herkenbare naam. Deze verschijnt in paragraaf 17 bij het koppelen van maatregelen, bijvoorbeeld "Onterechte identificatie van bezoekers".',
    'risk_description' => 'Beschrijving van het risico',
    'help_risk_description' => 'Welke negatieve gevolgen kunnen de gegevensverwerkingen hebben voor de rechten en vrijheden van de betrokkenen? Denk niet alleen aan privacy, maar bijvoorbeeld ook aan discriminatie of het onthouden van een voorziening.',
    'risk_origin' => 'Oorsprong',
    'help_risk_origin' => 'Waardoor kan dit risico ontstaan? Benoem de bron of gebeurtenis, bijvoorbeeld een menselijke fout, een storing of misbruik, een onbevoegde binnen of buiten de organisatie, een verwerker die zich niet aan de afspraken houdt, of een systeem dat onjuiste uitkomsten geeft.',
    'risk_likelihood' => 'Kans',
    'help_risk_likelihood' => 'Hoe waarschijnlijk is het dat dit gevolg intreedt?',
    'risk_likelihood_motivation' => 'Motivatie van de kans',
    'risk_impact' => 'Impact',
    'help_risk_impact' => 'Hoe ernstig is dit gevolg voor de betrokkenen wanneer het intreedt?',
    'risk_impact_motivation' => 'Motivatie van de impact',
    'risk_level' => 'Risiconiveau',
    'help_risk_level' => 'Wordt ingevuld zodra kans en impact bekend zijn. U mag daarvan afwijken, bijvoorbeeld wanneer een risico niet verder te mitigeren is; licht dat dan toe in de motivatie hiernaast.',
    'risk_level_motivation' => 'Motivatie van de risico-inschatting',
    'risks_additional_information' => "Aanvullende informatie over de risico's",
    'help_risks_additional_information' => 'Optioneel tekstveld voor extra toelichting.',
    'add_risk' => 'Risico toevoegen',
    'risk_item_label' => 'Risico',
    'risk_level_laag' => 'Laag',
    'risk_level_gemiddeld' => 'Gemiddeld',
    'risk_level_hoog' => 'Hoog',
    'risk_matrix_suggestion' => 'Risiconiveau :level volgt uit kans x impact.',
    'risk_matrix_deviation' => 'Let op: kans x impact wijst op :level. Licht de afwijking toe bij de motivatie van de risico-inschatting.',

    // 17. Maatregelen
    'measures' => 'Maatregelen',
    'measures_intro' => "Beoordeel welke technische, organisatorische en juridische maatregelen in redelijkheid kunnen worden getroffen om de hiervoor beschreven risico's te voorkomen of te verminderen. Beschrijf per maatregel welk risico deze aanpakt.",
    'measure' => 'Maatregel',
    'measure_description' => 'Beschrijving van de maatregel',
    'measure_type' => 'Soort maatregel',
    'help_measure_type' => 'Het model vraagt om technische, organisatorische en juridische maatregelen.',
    'measure_type_technisch' => 'Technisch',
    'measure_type_organisatorisch' => 'Organisatorisch',
    'measure_type_juridisch' => 'Juridisch',
    'measure_risks' => 'Welke risico\'s pakt deze maatregel aan?',
    'help_measure_risks' => "Kies een of meer risico's uit paragraaf 16. Vul eerst de risico's in; ze verschijnen hier automatisch.",
    'measure_risks_empty' => "Er zijn nog geen risico's ingevuld bij paragraaf 16. Vul die eerst in en sla op.",
    'measure_origin' => 'Herkomst van de maatregel',
    'measure_residual_level' => 'Resterend risico na deze maatregel',
    'help_measure_residual_level' => 'Welk risico blijft over nadat deze maatregel is uitgevoerd of geimplementeerd?',
    'measure_ap_advice' => 'Advies van de Autoriteit Persoonsgegevens',
    'help_measure_ap_advice' => 'Voeg een verwijzing naar of een beschrijving van het advies van de AP toe.',
    'measure_monitoring_country' => 'Land van monitoring en evaluatie',
    'help_measure_monitoring_country' => 'In welk land vindt de monitoring en evaluatie van de maatregelen plaats?',
    'help_measure_origin' => 'Waar komt deze maatregel vandaan? Bijvoorbeeld uit bestaand beleid, de BIO, een verwerkersovereenkomst, een advies van de FG of een eerdere DPIA.',
    'measure_owner' => 'Beheerder van de maatregel',
    'help_measure_owner' => 'Wie is verantwoordelijk voor het uitvoeren en bewaken van deze maatregel?',
    'measures_additional_information' => 'Aanvullende informatie over de maatregelen',
    'residual_risk_acceptance' => "Onderbouwing acceptatie resterende risico's",
    'help_residual_risk_acceptance' => "Geef een conclusie over de restrisico's. Zijn deze acceptabel? En is een voorafgaande raadpleging bij de Autoriteit Persoonsgegevens nodig?",
    'add_measure' => 'Maatregel toevoegen',
    'measure_item_label' => 'Maatregel',

    // Proces
    'data_subjects_consulted' => 'Betrokkenen of hun vertegenwoordigers zijn geconsulteerd',
    'help_data_subjects_consulted' => 'Artikel 35, negende lid, AVG vraagt waar passend om het advies van betrokkenen. Gaat het om eigen medewerkers, betrek dan de ondernemingsraad.',
    'data_subjects_consultation' => 'Uitkomst van de consultatie',
    'help_data_subjects_consultation' => 'Neem op wat de geconsulteerden hebben geadviseerd en wat daarmee is gedaan. Vindt geen consultatie plaats, motiveer die beslissing dan hier.',
    'fg_advice' => 'Advies van de functionaris voor gegevensbescherming',
    'help_fg_advice' => 'Het inwinnen van advies bij de FG is verplicht (artikel 35, tweede lid, AVG). Betrek de FG zo vroeg mogelijk en niet pas als het rapport af is.',
    'fg_advice_followup' => 'Wat is met het advies van de FG gedaan?',
    'fg_advice_received_at' => 'Datum advies FG',
    'ap_consultation_required' => 'Voorafgaande raadpleging van de AP is nodig',
    'help_ap_consultation_required' => 'Nodig wanneer uit de DPIA een hoog restrisico blijkt dat u niet tot een acceptabel niveau kunt terugbrengen (artikel 36 AVG). Bij een DPIA op regelgeving moet het voorstel altijd aan de AP worden voorgelegd.',
    'ap_consultation' => 'Advies van de AP en de opvolging daarvan',
    'help_ap_consultation' => 'Voor het schriftelijke advies van de AP geldt een termijn van acht weken, met een maximale verlenging van zes weken.',
    'ap_consultation_requested_at' => 'Datum raadpleging AP',
    'ap_consultation_warning' => "Een of meer maatregelen laten een hoog restrisico achter. Raadpleeg de Autoriteit Persoonsgegevens voordat met de verwerking wordt begonnen (artikel 36 AVG).",

    // Vaststelling en herziening
    'assessed_at' => 'Datum van uitvoering',
    'help_assessed_at' => 'De datum waarop deze DPIA is uitgevoerd of voor het laatst inhoudelijk is beoordeeld.',
    'review_at' => 'Datum volgende herziening',
    'help_review_at' => 'Een DPIA moet worden herzien als de verwerking wijzigt, en in ieder geval iedere drie jaar.',
    'review_hint' => 'Voorstel op basis van de uitvoeringsdatum: :date (drie jaar).',
    'management_summary' => 'Managementsamenvatting',
    'help_management_summary' => 'Een korte samenvatting van de uitkomsten voor bestuurders en besluitvormers.',

    // Koppelingen
    'avg_responsible_processing_records' => 'Verwerkingen (AVG verantwoordelijke)',
    'help_avg_responsible_processing_records' => 'Koppel de verwerkingen uit het register waarop deze DPIA betrekking heeft. Een DPIA mag een reeks vergelijkbare verwerkingen bestrijken.',
    'systems' => 'Systemen en applicaties',
    'processors' => 'Verwerkers',
    'responsibles' => 'Verwerkingsverantwoordelijken',

    // Overzicht
    'risk_count' => "Risico's",
    'highest_residual_risk' => 'Hoogste restrisico',
    'no_risks' => "Nog geen risico's",
    'review_due' => 'Herziening nodig',
];
