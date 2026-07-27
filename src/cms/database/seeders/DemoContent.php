<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Hand-authored Dutch content for DemoSeeder.
 *
 * Kept apart from the seeder itself so the seeder reads as structure (which
 * states exist, how entities relate) and this file reads as copy. Every string
 * here is written out rather than generated: a demo is clicked through in front
 * of a prospect, and faker latin behind the third tab undoes the whole pitch.
 *
 * The content is deliberately sector-neutral. Processings like personnel
 * administration, access control and CCTV exist verbatim at a municipality, a
 * hospital and an insurer alike, so the same register reads as plausible to
 * whichever audience is in the room.
 */
final class DemoContent
{
    /**
     * Organisations. Neutral registry-keeping bodies: recognisable as real
     * organisations without tying the demo to government or to healthcare.
     */
    public const ORGANISATIONS = [
        [
            'name' => 'Stichting Zorggroep Noorderbrug',
            'slug' => 'noorderbrug',
            'prefix' => 'ZGN',
            'legal_entity' => 'Stichting Zorggroep Noorderbrug',
            'review_months' => 12,
            'content' => 'Zorggroep Noorderbrug levert medisch-specialistische zorg vanuit drie '
                . 'locaties. In dit register publiceren wij de verwerkingen van persoonsgegevens '
                . 'waarvoor wij verwerkingsverantwoordelijke zijn.',
        ],
        [
            'name' => 'Gemeente Westerdam',
            'slug' => 'westerdam',
            'prefix' => 'GWD',
            'legal_entity' => 'Gemeente Westerdam',
            'review_months' => 24,
            'content' => 'De gemeente Westerdam verwerkt persoonsgegevens bij de uitvoering van haar '
                . 'wettelijke taken. Dit register geeft inzicht in welke gegevens wij verwerken en '
                . 'met welk doel.',
        ],
        [
            'name' => 'Waarborgfonds Meridiaan',
            'slug' => 'meridiaan',
            'prefix' => 'WFM',
            'legal_entity' => 'Waarborgfonds Meridiaan N.V.',
            'review_months' => 12,
            'content' => 'Waarborgfonds Meridiaan beheert collectieve verzekeringen en garantstellingen. '
                . 'Wij hechten aan transparantie over de persoonsgegevens die wij daarbij verwerken.',
        ],
    ];

    /**
     * Demo users. One per role, so any feature can be shown from the account
     * that would really perform it: the approval flow in particular only makes
     * sense when the mandate holder is a different person from the officer who
     * approved the version.
     */
    public const USERS = [
        ['name' => 'Sanne de Groot', 'email' => 'sanne.degroot', 'role' => 'chief-privacy-officer'],
        ['name' => 'Marieke de Vries', 'email' => 'marieke.devries', 'role' => 'mandate-holder'],
        ['name' => 'Joost Bakker', 'email' => 'joost.bakker', 'role' => 'privacy-officer'],
        ['name' => 'Fatima El Amrani', 'email' => 'fatima.elamrani', 'role' => 'input-processor'],
        ['name' => 'Tom Hendriks', 'email' => 'tom.hendriks', 'role' => 'input-processor-databreach'],
        ['name' => 'Hans Willems', 'email' => 'hans.willems', 'role' => 'data-protection-official'],
        ['name' => 'Els Vermeer', 'email' => 'els.vermeer', 'role' => 'counselor'],
    ];

    /** Departments, used for the "AVG Verantwoordelijke Dienst" field. */
    public const SERVICES = [
        'Directie Bedrijfsvoering',
        'Directie Informatievoorziening',
        'Directie Juridische Zaken',
        'Directie Personeel en Organisatie',
        'Directie Publieke Dienstverlening',
        'Directie Financiën en Control',
    ];

    /** Labels shown on records; a mix a privacy officer would really apply. */
    public const TAGS = [
        'Bijzondere persoonsgegevens',
        'Cameratoezicht',
        'DPIA uitgevoerd',
        'Extern gedeeld',
        'Financieel',
        'Hoog risico',
        'Medewerkers',
        'Publiek toegankelijk',
        'Verwerker betrokken',
        'Wettelijke verplichting',
    ];

    /** Document types for the document register. */
    public const DOCUMENT_TYPES = [
        'DPIA',
        'Verwerkersovereenkomst',
        'Beveiligingsbeleid',
        'Bewaartermijnenbeleid',
        'Toestemmingsformulier',
        'Auditrapport',
    ];

    /** Positions for contact persons. */
    public const CONTACT_POSITIONS = [
        'Privacy Officer',
        'Functionaris Gegevensbescherming',
        'Informatiebeveiligingsadviseur',
        'Teamleider',
        'Applicatiebeheerder',
    ];

    /**
     * Systems in which processing takes place.
     */
    public const SYSTEMS = [
        'AFAS Profit — personeels- en salarisadministratie',
        'TOPdesk — meldingen en serviceverzoeken',
        'Microsoft 365 — kantoorautomatisering en e-mail',
        'Centraal dossiersysteem',
        'Genetec Security Center — cameratoezicht',
        'Active Directory — toegangs- en rechtenbeheer',
        'Zaaksysteem Djuma',
        'Exact Online — financiële administratie',
    ];

    /**
     * Processors, with plausible contact details. Emails use example.com so
     * nothing in the demo can send mail to a real address.
     */
    public const PROCESSORS = [
        ['name' => 'AFAS Software B.V.', 'email' => 'privacy@afas.example.com', 'phone' => '+31331234567'],
        ['name' => 'TOPdesk Nederland B.V.', 'email' => 'privacy@topdesk.example.com', 'phone' => '+31152345678'],
        ['name' => 'Cloudbeheer Nederland B.V.', 'email' => 'avg@cloudbeheer.example.com', 'phone' => '+31203456789'],
        ['name' => 'Archiefdiensten Delta B.V.', 'email' => 'privacy@delta-archief.example.com', 'phone' => '+31304567890'],
        ['name' => 'Salarisverwerking Contour B.V.', 'email' => 'fg@contour.example.com', 'phone' => '+31105678901'],
    ];

    /**
     * Recipients of personal data. Described rather than named, matching how
     * the field is used in the application.
     */
    public const RECEIVERS = [
        'Belastingdienst — in het kader van de loonaangifte',
        'UWV — voor de uitvoering van de sociale zekerheidswetgeving',
        'Pensioenfonds — voor de pensioenadministratie van medewerkers',
        'Arbodienst — voor de verzuimbegeleiding',
        'Zorgverzekeraars — voor de declaratie van geleverde zorg',
        'Nationale politie — uitsluitend na een rechtmatig vorderingsbevel',
        'Accountant — in het kader van de jaarrekeningcontrole',
    ];

    /** Legal bases, matching the options the application offers. */
    public const LEGAL_BASE_LEGAL_OBLIGATION = 'Wettelijke verplichting';
    public const LEGAL_BASE_AGREEMENT = 'Uitvoering overeenkomst';
    public const LEGAL_BASE_PUBLIC_TASK = 'Vervulling taak van algemeen belang';
    public const LEGAL_BASE_CONSENT = 'Toestemming betrokkene';
    public const LEGAL_BASE_LEGITIMATE_INTEREST = 'Gerechtvaardigd belang';

    /**
     * The AVG responsible processing records: the register the demo opens on.
     *
     * The set is chosen to span the states worth demonstrating rather than to
     * be exhaustive — see DemoSeeder for how `state` and `review_offset_months`
     * are turned into version histories and review warnings.
     */
    public const AVG_RECORDS = [
        [
            'name' => 'Personeels- en salarisadministratie',
            'retention' => '7 jaar na uitdiensttreding, op grond van de fiscale bewaarplicht.',
            'service' => 'Directie Personeel en Organisatie',
            'goal' => 'Het voeren van de personeels- en salarisadministratie, inclusief het uitbetalen '
                . 'van salarissen en het afdragen van loonheffing.',
            'legal_base' => self::LEGAL_BASE_LEGAL_OBLIGATION,
            'stakeholder' => 'Medewerkers in loondienst',
            'data_items' => ['NAW-gegevens', 'Burgerservicenummer', 'Bankrekeningnummer', 'Salarisgegevens', 'Contractgegevens'],
            'special_data' => false,
            'bsn' => true,
            'systems' => [0, 5],
            'processors' => [0, 4],
            'receivers' => [0, 1, 2],
            'tags' => [4, 6, 9],
            'state' => 'established',
            'review_offset_months' => 8,
            'dpia' => true,
            'outside_eu' => false,
            'description' => 'De salarisadministratie wordt uitgevoerd door een externe verwerker. '
                . 'De verwerkingsverantwoordelijke bepaalt doel en middelen; de uitvoering is belegd '
                . 'bij de directie Personeel en Organisatie.',
        ],
        [
            'name' => 'Verzuimbegeleiding en re-integratie',
            'retention' => '2 jaar na afronding van het verzuimtraject.',
            'service' => 'Directie Personeel en Organisatie',
            'goal' => 'Het begeleiden van zieke medewerkers en het uitvoeren van de re-integratie '
                . 'conform de Wet verbetering poortwachter.',
            'legal_base' => self::LEGAL_BASE_LEGAL_OBLIGATION,
            'stakeholder' => 'Medewerkers met een verzuimmelding',
            'data_items' => ['NAW-gegevens', 'Verzuimdata', 'Functionele beperkingen', 'Re-integratieafspraken'],
            'special_data' => true,
            'bsn' => true,
            'systems' => [0],
            'processors' => [0],
            'receivers' => [1, 3],
            'tags' => [0, 4, 5],
            'state' => 'established',
            // Overdue: shows how a lapsed periodic review reads on the dashboard.
            'review_offset_months' => -5,
            'dpia' => true,
            'outside_eu' => false,
            'description' => 'Gezondheidsgegevens worden uitsluitend verwerkt door de arbodienst. '
                . 'De werkgever ontvangt alleen gegevens over de belastbaarheid, niet over de '
                . 'medische oorzaak van het verzuim.',
        ],
        [
            'name' => 'Cameratoezicht toegangsbeveiliging',
            'retention' => '28 dagen, tenzij beelden nodig zijn voor de afhandeling van een incident.',
            'service' => 'Directie Bedrijfsvoering',
            'goal' => 'Het beveiligen van gebouwen en eigendommen en het beschermen van de veiligheid '
                . 'van medewerkers en bezoekers.',
            'legal_base' => self::LEGAL_BASE_LEGITIMATE_INTEREST,
            'stakeholder' => 'Bezoekers en medewerkers van de locaties',
            'data_items' => ['Camerabeelden', 'Datum en tijdstip', 'Locatie van de opname'],
            'special_data' => false,
            'bsn' => false,
            'systems' => [4],
            'processors' => [2],
            'receivers' => [5],
            'tags' => [1, 5, 8],
            'state' => 'in_review',
            'review_offset_months' => 11,
            'dpia' => true,
            'outside_eu' => false,
            'description' => 'Camerabeelden worden na 28 dagen automatisch verwijderd, tenzij zij nodig '
                . 'zijn voor de afhandeling van een incident. Beelden worden alleen aan de politie '
                . 'verstrekt na een rechtmatig vorderingsbevel.',
        ],
        [
            'name' => 'Toegangsbeheer informatiesystemen',
            'retention' => 'Logging 6 maanden; autorisatiegegevens tot 1 jaar na uitdiensttreding.',
            'service' => 'Directie Informatievoorziening',
            'goal' => 'Het toekennen, wijzigen en intrekken van toegangsrechten tot informatiesystemen '
                . 'en het vastleggen van logging ten behoeve van informatiebeveiliging.',
            'legal_base' => self::LEGAL_BASE_LEGITIMATE_INTEREST,
            'stakeholder' => 'Medewerkers en ingehuurd personeel',
            'data_items' => ['Gebruikersnaam', 'Functie en afdeling', 'Toegangsrechten', 'Inloggegevens'],
            'special_data' => false,
            'bsn' => false,
            'systems' => [5, 1],
            'processors' => [2],
            'receivers' => [],
            'tags' => [4, 8],
            'state' => 'approved',
            'review_offset_months' => 18,
            'dpia' => false,
            'outside_eu' => false,
            'description' => 'Logging wordt uitsluitend gebruikt voor informatiebeveiliging en niet voor '
                . 'de beoordeling van medewerkers.',
        ],
        [
            'name' => 'Afhandelen vragen en klachten',
            'retention' => '2 jaar na afhandeling van de melding.',
            'service' => 'Directie Publieke Dienstverlening',
            'goal' => 'Het in behandeling nemen en afhandelen van vragen, meldingen en klachten van '
                . 'betrokkenen.',
            'legal_base' => self::LEGAL_BASE_PUBLIC_TASK,
            'stakeholder' => 'Personen die contact opnemen met de organisatie',
            'data_items' => ['NAW-gegevens', 'Contactgegevens', 'Inhoud van de melding', 'Correspondentie'],
            'special_data' => false,
            'bsn' => false,
            'systems' => [1, 6],
            'processors' => [1],
            'receivers' => [],
            'tags' => [7, 9],
            'state' => 'established',
            'review_offset_months' => 20,
            'dpia' => false,
            'outside_eu' => false,
            'description' => 'Meldingen worden na afhandeling nog twee jaar bewaard ten behoeve van '
                . 'kwaliteitsverbetering en verantwoording.',
        ],
        [
            'name' => 'Inkoop- en leveranciersadministratie',
            'retention' => '7 jaar na afloop van het boekjaar, op grond van de fiscale bewaarplicht.',
            'service' => 'Directie Financiën en Control',
            'goal' => 'Het vastleggen van inkooporders, contracten en facturen en het uitvoeren van '
                . 'de daarbij behorende betalingen.',
            'legal_base' => self::LEGAL_BASE_AGREEMENT,
            'stakeholder' => 'Contactpersonen bij leveranciers',
            'data_items' => ['Naam en functie', 'Zakelijke contactgegevens', 'Bankrekeningnummer'],
            'special_data' => false,
            'bsn' => false,
            'systems' => [7],
            'processors' => [],
            'receivers' => [6],
            'tags' => [3],
            'state' => 'established',
            'review_offset_months' => 30,
            'dpia' => false,
            'outside_eu' => false,
            'description' => 'Financiële gegevens worden zeven jaar bewaard op grond van de fiscale '
                . 'bewaarplicht.',
        ],
        [
            'name' => 'Klanttevredenheidsonderzoek',
            'retention' => '12 maanden na afronding van het onderzoek.',
            'service' => 'Directie Publieke Dienstverlening',
            'goal' => 'Het meten van de tevredenheid over de dienstverlening ten behoeve van '
                . 'kwaliteitsverbetering.',
            'legal_base' => self::LEGAL_BASE_CONSENT,
            'stakeholder' => 'Betrokkenen die een dienst hebben afgenomen',
            'data_items' => ['E-mailadres', 'Gegeven antwoorden', 'Datum van deelname'],
            'special_data' => false,
            'bsn' => false,
            'systems' => [2],
            'processors' => [2],
            'receivers' => [],
            'tags' => [3, 7],
            'state' => 'draft',
            'review_offset_months' => 12,
            'dpia' => false,
            'outside_eu' => true,
            'description' => 'De enquêtetool verwerkt gegevens op servers buiten de EER. Hiervoor zijn '
                . 'standaardcontractbepalingen afgesloten en is een transfer impact assessment '
                . 'uitgevoerd.',
        ],
        [
            'name' => 'Archivering en informatiebeheer',
            'retention' => 'Conform de vastgestelde selectielijst, varierend van 5 jaar tot blijvende bewaring.',
            'service' => 'Directie Juridische Zaken',
            'goal' => 'Het duurzaam bewaren en beheren van archiefbescheiden conform de geldende '
                . 'selectielijst en bewaartermijnen.',
            'legal_base' => self::LEGAL_BASE_LEGAL_OBLIGATION,
            'stakeholder' => 'Alle betrokkenen van wie stukken zijn gearchiveerd',
            'data_items' => ['Dossiergegevens', 'Correspondentie', 'Besluiten'],
            'special_data' => false,
            'bsn' => true,
            'systems' => [3, 6],
            'processors' => [3],
            'receivers' => [],
            'tags' => [9],
            'state' => 'obsolete',
            'review_offset_months' => 36,
            'dpia' => false,
            'outside_eu' => false,
            'description' => 'Deze verwerking is opgegaan in de bredere verwerking informatiebeheer. '
                . 'De vastgestelde versie is daarmee komen te vervallen.',
        ],
    ];

    /**
     * Processor-side records: processings the organisation carries out on
     * behalf of another controller. A smaller set, since the register exists
     * mainly to show that the distinction is modelled.
     */
    public const AVG_PROCESSOR_RECORDS = [
        [
            'name' => 'Salarisverwerking voor aangesloten stichtingen',
            'goal' => 'Het verwerken van de salarisadministratie in opdracht van aangesloten '
                . 'stichtingen die gebruikmaken van de gedeelde bedrijfsvoering.',
            'controller' => 'Aangesloten stichtingen binnen het samenwerkingsverband',
            'state' => 'established',
            'description' => 'De verwerking vindt uitsluitend plaats op basis van een '
                . 'verwerkersovereenkomst en schriftelijke instructies van de verantwoordelijke.',
        ],
        [
            'name' => 'Hosting en beheer van het zaaksysteem',
            'goal' => 'Het technisch beheren en hosten van het zaaksysteem waarin partnerorganisaties '
                . 'hun eigen dossiers verwerken.',
            'controller' => 'Partnerorganisaties met een aansluitovereenkomst',
            'state' => 'in_review',
            'description' => 'Beheerders hebben uitsluitend toegang tot persoonsgegevens voor zover '
                . 'noodzakelijk voor technisch beheer. Toegang wordt gelogd.',
        ],
    ];

    /**
     * WPG records. Only relevant to organisations with a police or enforcement
     * task, so a modest set on the municipality is enough to show the register
     * exists and has its own field set.
     */
    public const WPG_RECORDS = [
        [
            'name' => 'Handhaving openbare ruimte door boa\'s',
            'goal' => 'Het vastleggen van constateringen en opgemaakte processen-verbaal door '
                . 'buitengewoon opsporingsambtenaren.',
            'state' => 'established',
            'description' => 'Gegevens worden verwerkt op grond van de Wet politiegegevens en zijn '
                . 'uitsluitend toegankelijk voor daartoe geautoriseerde medewerkers.',
        ],
        [
            'name' => 'Registratie van meldingen overlast',
            'goal' => 'Het registreren en opvolgen van meldingen van overlast in de openbare ruimte.',
            'state' => 'in_review',
            'description' => 'Meldingen worden na afhandeling maximaal vijf jaar bewaard conform de '
                . 'bewaartermijnen van de Wpg.',
        ],
    ];

    /**
     * Algorithm register entries. The states differ on purpose: one in
     * production and published, one still in development, so the register can
     * be shown to track algorithms across their lifecycle.
     */
    public const ALGORITHM_RECORDS = [
        [
            'name' => 'Risicoselectie bij aanvraagbeoordeling',
            'theme' => 'Openbare orde en veiligheid',
            'status' => 'In gebruik',
            'category' => 'Impactvol algoritme',
            'description' => 'Een op regels gebaseerd model dat aanvragen ordent op de kans dat '
                . 'aanvullende controle nodig is, zodat behandelaars hun aandacht gericht kunnen '
                . 'inzetten.',
            'goal_and_impact' => 'Het doel is een efficiëntere inzet van behandelcapaciteit. Het '
                . 'algoritme neemt geen besluit: het bepaalt uitsluitend de volgorde waarin '
                . 'aanvragen door een behandelaar worden beoordeeld.',
            'considerations' => 'Er is bewust gekozen voor een regelgebaseerd model in plaats van een '
                . 'lerend model, zodat iedere uitkomst herleidbaar en uitlegbaar is aan de betrokkene.',
            'human_intervention' => 'Elke aanvraag wordt door een medewerker beoordeeld. De medewerker '
                . 'kan de volgorde negeren en legt afwijkingen vast.',
            'risk_analysis' => 'Er is een DPIA en een mensenrechtentoets (IAMA) uitgevoerd. Het model '
                . 'gebruikt geen gegevens over nationaliteit, etniciteit of postcode.',
            'supplier' => 'In eigen beheer ontwikkeld',
            'state' => 'established',
            'published' => true,
        ],
        [
            'name' => 'Automatische classificatie van inkomende post',
            'theme' => 'Organisatie en bedrijfsvoering',
            'status' => 'In ontwikkeling',
            'category' => 'Overige algoritmes',
            'description' => 'Een tekstclassificatiemodel dat inkomende berichten toewijst aan het '
                . 'juiste behandelteam.',
            'goal_and_impact' => 'Het doel is het verkorten van doorlooptijden door berichten direct '
                . 'bij het juiste team te laten uitkomen. De impact op betrokkenen is beperkt: een '
                . 'onjuiste toewijzing leidt tot een interne doorverwijzing.',
            'considerations' => 'Overwogen is of handmatige triage volstaat. Gezien het volume is '
                . 'gekozen voor ondersteuning door een model, met steekproefsgewijze controle.',
            'human_intervention' => 'Behandelaars kunnen een toewijzing altijd corrigeren. Correcties '
                . 'worden gebruikt om het model te verbeteren.',
            'risk_analysis' => 'De DPIA is in concept gereed en wordt voor ingebruikname vastgesteld.',
            'supplier' => 'Cloudbeheer Nederland B.V.',
            'state' => 'draft',
            'published' => false,
        ],
    ];

    /**
     * Data breach register. Spread across the lifecycle on purpose: one closed
     * and reported to the supervisory authority, one still open, one assessed
     * as not reportable — the three outcomes a privacy officer actually deals
     * with.
     */
    public const DATA_BREACH_RECORDS = [
        [
            'name' => 'Verkeerd geadresseerde e-mail met deelnemerslijst',
            'type' => 'Persoonsgegevens verstuurd aan verkeerde ontvanger',
            'summary' => 'Een medewerker heeft een deelnemerslijst per e-mail verstuurd aan een '
                . 'onjuiste ontvanger. De lijst bevatte namen en e-mailadressen van 42 personen.',
            'involved_people' => '42 deelnemers aan een informatiebijeenkomst',
            'estimated_risk' => 'Beperkt risico: het betreft geen bijzondere persoonsgegevens. De '
                . 'ontvanger heeft schriftelijk bevestigd het bericht te hebben verwijderd.',
            'measures' => 'De ontvanger is direct verzocht het bericht te verwijderen. Betrokkenen '
                . 'zijn geïnformeerd. Er is een aanvullende instructie verspreid over het gebruik '
                . 'van BCC bij groepsberichten.',
            'ap_reported' => true,
            'fg_reported' => true,
            'reported_to_involved' => true,
            'discovered_offset_days' => 45,
            'closed' => true,
        ],
        [
            'name' => 'Verlies van een onversleutelde USB-stick',
            'type' => 'Verlies van gegevensdrager',
            'summary' => 'Een medewerker is tijdens woon-werkverkeer een USB-stick verloren. Onbekend '
                . 'is of de stick persoonsgegevens bevatte; de medewerker verklaart uitsluitend '
                . 'werkdocumenten te hebben opgeslagen.',
            'involved_people' => 'Onbekend, mogelijk enkele tientallen betrokkenen',
            'estimated_risk' => 'Risico wordt onderzocht. Zolang de inhoud niet is vastgesteld, wordt '
                . 'uitgegaan van een verhoogd risico.',
            'measures' => 'Onderzoek naar de inhoud loopt. Vooruitlopend hierop is het gebruik van '
                . 'niet-versleutelde gegevensdragers organisatiebreed geblokkeerd.',
            'ap_reported' => false,
            'fg_reported' => true,
            'reported_to_involved' => false,
            'discovered_offset_days' => 4,
            'closed' => false,
        ],
        [
            'name' => 'Inzage in dossier door onbevoegde medewerker',
            'type' => 'Onbevoegde toegang tot persoonsgegevens',
            'summary' => 'Uit logginganalyse bleek dat een medewerker een dossier heeft geraadpleegd '
                . 'zonder dat daar een behandelrelatie voor bestond.',
            'involved_people' => '1 betrokkene',
            'estimated_risk' => 'Geen doorgifte aan derden vastgesteld. De gegevens zijn niet '
                . 'gewijzigd of gekopieerd.',
            'measures' => 'De autorisatie van de medewerker is ingetrokken en er heeft een gesprek '
                . 'plaatsgevonden. De betrokkene is geïnformeerd. De logginganalyse wordt voortaan '
                . 'maandelijks uitgevoerd.',
            'ap_reported' => false,
            'fg_reported' => true,
            'reported_to_involved' => true,
            'discovered_offset_days' => 120,
            'closed' => true,
        ],
    ];

    /**
     * Documents. The expiry offsets drive the manual's "verlopen
     * documenttermijnen" warnings: one already expired, one expiring shortly,
     * the rest comfortably valid.
     */
    public const DOCUMENTS = [
        [
            'name' => 'DPIA Personeels- en salarisadministratie',
            'type' => 'DPIA',
            'location' => 'Documentbeheer — map Privacy/DPIA',
            'expires_offset_months' => 9,
        ],
        [
            'name' => 'DPIA Cameratoezicht',
            'type' => 'DPIA',
            'location' => 'Documentbeheer — map Privacy/DPIA',
            'expires_offset_months' => -2,
        ],
        [
            // Expired: a lapsed processor agreement is the classic finding in an
            // audit, and shows a second document type on the overdue list.
            'name' => 'Verwerkersovereenkomst Salarisservice Van Dijk B.V.',
            'type' => 'Verwerkersovereenkomst',
            'location' => 'Contractbeheer — dossier leveranciers',
            'expires_offset_months' => -7,
        ],
        [
            // Expired longest, so it heads the list and shows the ordering.
            'name' => 'Bewerkersprotocol gemeentelijke basisadministratie',
            'type' => 'Beveiligingsbeleid',
            'location' => 'Intranet — Beleid en kaders',
            'expires_offset_months' => -14,
        ],
        [
            'name' => 'Verwerkersovereenkomst AFAS Software B.V.',
            'type' => 'Verwerkersovereenkomst',
            'location' => 'Contractbeheer — dossier leveranciers',
            'expires_offset_months' => 1,
        ],
        [
            'name' => 'Verwerkersovereenkomst Cloudbeheer Nederland B.V.',
            'type' => 'Verwerkersovereenkomst',
            'location' => 'Contractbeheer — dossier leveranciers',
            'expires_offset_months' => 14,
        ],
        [
            'name' => 'Informatiebeveiligingsbeleid',
            'type' => 'Beveiligingsbeleid',
            'location' => 'Intranet — Beleid en kaders',
            'expires_offset_months' => 22,
        ],
        [
            'name' => 'Bewaartermijnenbeleid en selectielijst',
            'type' => 'Bewaartermijnenbeleid',
            'location' => 'Intranet — Beleid en kaders',
            'expires_offset_months' => 30,
        ],
    ];

    /**
     * Remarks left on records, and FG remarks. Written as the kind of note a
     * reviewer really leaves, so the collaboration features read as used.
     */
    public const REMARKS = [
        'Bewaartermijn afgestemd met de archivaris; selectielijst is leidend.',
        'Verwerkersovereenkomst is getekend ontvangen en toegevoegd aan het dossier.',
        'Na de laatste wijziging in het systeem opnieuw beoordeeld: geen gevolgen voor de grondslag.',
        'Betrokkenen worden geïnformeerd via de privacyverklaring op de website.',
    ];

    /**
     * FG remarks are visible only to the Functionaris Gegevensbescherming, so
     * they read as supervisory notes rather than general comments.
     */
    public const FG_REMARKS = [
        'Aandachtspunt: controleer bij de volgende review of de bewaartermijn nog aansluit op de '
            . 'selectielijst.',
        'De DPIA is uitgevoerd maar verloopt binnenkort. Tijdig laten actualiseren.',
        'Grondslag gerechtvaardigd belang is onderbouwd met een belangenafweging. Akkoord.',
    ];

    /** Version-history notes shown in the approval trail. */
    public const SNAPSHOT_NOTES = [
        'Jaarlijkse review uitgevoerd, geen inhoudelijke wijzigingen.',
        'Verwerker toegevoegd na aanbesteding.',
        'Bewaartermijn aangepast conform de nieuwe selectielijst.',
    ];

    /** Reason recorded when a mandate holder declines a version. */
    public const DECLINE_REASON = 'De omschrijving van de bewaartermijn is nog niet concreet genoeg. '
        . 'Graag aanvullen met de exacte termijn per gegevenscategorie voordat ik akkoord geef.';
}
