<?php

declare(strict_types=1);

namespace App\Manual\Content;

use App\Enums\Authorization\Role;
use App\Manual\FeatureGate;
use App\Manual\Step;
use App\Manual\Task;
use App\Manual\TaskRoles;

/**
 * The task layer: "wat wilt u doen?".
 *
 * Tasks are the entry point. Every step is a sentence or two and then points
 * into the reference layer, which is where the explanation lives. Nothing here
 * restates a screen: if a step needs a paragraph, that paragraph belongs in a
 * topic in ReferenceContent and the step links to it.
 */
final class TaskContent
{
    public const GROUP_REGISTREREN = 'Vastleggen';
    public const GROUP_VASTSTELLEN = 'Laten vaststellen';
    public const GROUP_BEHEREN = 'Beheren';
    public const GROUP_TERUGVINDEN = 'Terugvinden en delen';

    /**
     * The groups in the order they appear on the page.
     *
     * @return array<string, string>
     */
    public static function groups(): array
    {
        return [
            self::GROUP_REGISTREREN => 'Gegevens in het register zetten.',
            self::GROUP_VASTSTELLEN => 'Een versie door het goedkeuringsproces leiden.',
            self::GROUP_TERUGVINDEN => 'Vinden, ordenen en naar buiten brengen.',
            self::GROUP_BEHEREN => 'De inrichting van het portaal onderhouden.',
        ];
    }

    /**
     * @return array<Task>
     */
    public static function tasks(): array
    {
        return [
            self::verwerkingVastleggen(),
            self::wpgVerwerkingVastleggen(),
            self::datalekMelden(),
            self::versieAanmakenEnLatenGoedkeuren(),
            self::labelsGebruiken(),
            self::overzichtOpvragen(),
            self::verwerkingPubliceren(),
            self::gebruikersEnRollenBeheren(),
        ];
    }

    private static function verwerkingVastleggen(): Task
    {
        return new Task(
            id: 'verwerking-vastleggen',
            group: self::GROUP_REGISTREREN,
            title: 'Een verwerking vastleggen',
            summary: 'Een nieuwe verwerking in het register zetten.',
            intro: 'U legt een verwerking van persoonsgegevens vast in een van de '
                . 'verwerkingsregisters. U kunt tussentijds opslaan: pas bij het aanmaken van een '
                . 'versie wordt gecontroleerd of alles is ingevuld.',
            steps: [
                new Step(
                    title: 'Open het register en maak een verwerking aan',
                    body: 'Kies in het navigatiemenu het register en klik op "Verwerking '
                        . 'aanmaken". U komt op de detailpagina.',
                    topicIds: ['verwerkingsregisters'],
                ),
                new Step(
                    title: 'Vul de gegevens in',
                    body: 'Loop de domeinen langs met het navigatiemenu rechts. Tussentijds '
                        . 'opslaan mag; verplichte velden worden pas bij de versie gecontroleerd.',
                    topicIds: ['verwerkingsregisters'],
                ),
                new Step(
                    title: 'Vul de bewaartermijnen in',
                    body: 'Kies per gegeven een bewaartermijn uit de lijst, of beschrijf de '
                        . 'termijn zelf als geen van de opties past.',
                    topicIds: ['bewaartermijnen'],
                ),
                new Step(
                    title: 'Geef de verwerking een label',
                    body: 'Ken in het veld "Labels" de afdeling, locatie of het werkterrein toe, '
                        . 'zodat u de verwerking later terugvindt.',
                    topicIds: ['labels-toekennen'],
                ),
            ],
            roles: new TaskRoles(
                performers: [Role::INPUT_PROCESSOR, Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL, Role::MANDATE_HOLDER],
            ),
            done: 'De verwerking staat in het register en kan worden aangevuld. Is hij compleet, '
                . 'maak dan een versie aan.',
        );
    }

    private static function wpgVerwerkingVastleggen(): Task
    {
        return new Task(
            id: 'wpg-verwerking-vastleggen',
            group: self::GROUP_REGISTREREN,
            title: 'Een Wpg-verwerking vastleggen',
            summary: 'Een verwerking die onder de Wet politiegegevens valt.',
            intro: 'Het register WPG Verantwoordelijke Verwerkingen werkt hetzelfde als de '
                . 'AVG-registers; alleen de opzoeklijst eromheen is eigen.',
            steps: [
                new Step(
                    title: 'Open het WPG-register',
                    body: 'Kies "WPG Verantwoordelijke Verwerkingen" in het navigatiemenu en klik '
                        . 'op "Verwerking aanmaken".',
                    topicIds: ['wpg-register'],
                ),
                new Step(
                    title: 'Vul de gegevens in',
                    body: 'De detailpagina werkt gelijk aan die van de AVG-registers.',
                    topicIds: ['wpg-register', 'verwerkingsregisters'],
                ),
                new Step(
                    title: 'Maak een versie aan',
                    body: 'Ook het goedkeuringsproces is hetzelfde als bij de andere registers.',
                    topicIds: ['versie-aanmaken'],
                ),
            ],
            roles: new TaskRoles(
                performers: [Role::INPUT_PROCESSOR, Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL, Role::MANDATE_HOLDER],
            ),
            gate: FeatureGate::WPG,
            done: 'De Wpg-verwerking staat in het register.',
        );
    }

    private static function datalekMelden(): Task
    {
        return new Task(
            id: 'datalek-melden',
            group: self::GROUP_REGISTREREN,
            title: 'Een datalek melden',
            summary: 'Een datalek vastleggen en zo nodig doorgeven.',
            intro: 'U registreert een datalek in het datalekregister. Geeft u aan dat het lek bij '
                . 'de Autoriteit Persoonsgegevens is gemeld, dan gaat er automatisch bericht naar '
                . 'de Chief Privacy Officers en Mandaathouders.',
            steps: [
                new Step(
                    title: 'Maak het datalek aan',
                    body: 'Open het datalekregister en klik op "Datalek aanmaken".',
                    topicIds: ['datalekken'],
                ),
                new Step(
                    title: 'Leg vast wat er gebeurd is',
                    body: 'Vul de gegevens van het lek in, inclusief de datum van melding.',
                    topicIds: ['datalekken'],
                ),
                new Step(
                    title: 'Geef aan of het bij de AP is gemeld',
                    body: 'Zet het vinkje en sla op. Daarmee gaat het bericht naar de Chief '
                        . 'Privacy Officers en Mandaathouders van de organisatie.',
                    topicIds: ['datalekken', 'notificaties'],
                ),
            ],
            roles: new TaskRoles(
                performers: [
                    Role::INPUT_PROCESSOR_DATABREACH,
                    Role::CHIEF_PRIVACY_OFFICER,
                    Role::PRIVACY_OFFICER,
                ],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL],
            ),
            done: 'Het datalek staat in het register en de betrokkenen zijn op de hoogte.',
        );
    }

    private static function versieAanmakenEnLatenGoedkeuren(): Task
    {
        return new Task(
            id: 'versie-laten-vaststellen',
            group: self::GROUP_VASTSTELLEN,
            title: 'Een versie aanmaken en laten goedkeuren',
            summary: 'Een verwerking door het goedkeuringsproces leiden.',
            intro: 'Is een verwerking compleet, dan maakt u er een versie van. Die versie gaat '
                . 'langs een Privacy Officer en wordt vastgesteld, eventueel nadat Mandaathouders '
                . 'akkoord hebben gegeven.',
            steps: [
                new Step(
                    title: 'Maak een versie aan',
                    body: 'Klik rechtsbovenin op "Versie aanmaken". Ontbreken er verplichte '
                        . 'velden, dan ziet u nu welke.',
                    topicIds: ['versie-aanmaken'],
                ),
                new Step(
                    title: 'Koppel eventueel Mandaathouders',
                    body: 'Werkt uw organisatie met Mandaathouders, voeg ze dan toe onder '
                        . '"Ondertekeningen".',
                    topicIds: ['versie-aanmaken', 'akkoord-geven'],
                ),
                new Step(
                    title: 'Laat de versie goedkeuren',
                    body: 'Een Privacy Officer beoordeelt de versie en keurt hem goed.',
                    topicIds: ['goedkeuren', 'versiestatussen'],
                ),
                new Step(
                    title: 'Laat de versie vaststellen',
                    body: 'Na goedkeuring - en eventuele akkoorden - stelt een Privacy Officer de '
                        . 'versie vast. Die geldt dan als de geldende versie.',
                    topicIds: ['vaststellen', 'versiestatussen'],
                ),
            ],
            roles: new TaskRoles(
                performers: [Role::INPUT_PROCESSOR, Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                readers: [
                    Role::MANDATE_HOLDER,
                    Role::COUNSELOR,
                    Role::DATA_PROTECTION_OFFICIAL,
                ],
            ),
            done: 'De versie is vastgesteld en geldt als de geldende versie van de verwerking.',
        );
    }

    private static function labelsGebruiken(): Task
    {
        return new Task(
            id: 'labels-gebruiken',
            group: self::GROUP_TERUGVINDEN,
            title: 'Labels gebruiken',
            summary: 'De registratie indelen naar uw eigen organisatie.',
            intro: 'Met labels legt u vast wáár een verwerking thuishoort: bij welke afdeling, op '
                . 'welke locatie, binnen welk werkterrein. Daarmee vindt u alles terug op uw eigen '
                . 'indeling.',
            steps: [
                new Step(
                    title: 'Bepaal welke indeling u aanhoudt',
                    body: 'Spreek binnen de organisatie af welke labels er zijn en hoe u ze '
                        . 'schrijft.',
                    topicIds: ['waarom-labels'],
                ),
                new Step(
                    title: 'Maak de labels aan',
                    body: 'Een (Chief) Privacy Officer beheert de labels onder "Beheer" > '
                        . '"Labels".',
                    topicIds: ['labels-beheren'],
                ),
                new Step(
                    title: 'Ken labels toe',
                    body: 'Gebruik het veld "Labels" op de detailpagina van een verwerking, een '
                        . 'systeem of een document.',
                    topicIds: ['labels-toekennen'],
                ),
                new Step(
                    title: 'Filter erop',
                    body: 'Gebruik de filterknop boven een overzicht om alles van één label bij '
                        . 'elkaar te zien.',
                    topicIds: ['filteren-op-labels'],
                ),
            ],
            roles: new TaskRoles(
                performers: [
                    Role::INPUT_PROCESSOR,
                    Role::INPUT_PROCESSOR_DATABREACH,
                    Role::CHIEF_PRIVACY_OFFICER,
                    Role::PRIVACY_OFFICER,
                ],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL, Role::MANDATE_HOLDER],
            ),
            done: 'De registratie is ingedeeld op uw eigen organisatiekenmerken.',
        );
    }

    private static function overzichtOpvragen(): Task
    {
        return new Task(
            id: 'overzicht-opvragen',
            group: self::GROUP_TERUGVINDEN,
            title: 'Een overzicht opvragen of exporteren',
            summary: 'Een selectie uit een register naar csv of xlsx.',
            intro: 'U maakt een overzicht door een tabel te filteren en het resultaat te '
                . 'exporteren. De export volgt de filters, dus filtert u eerst, dan krijgt u '
                . 'precies die selectie.',
            steps: [
                new Step(
                    title: 'Filter de tabel',
                    body: 'Gebruik de filterknop rechtsboven het overzicht, bijvoorbeeld op label '
                        . 'of op status.',
                    topicIds: ['filteren-op-labels'],
                ),
                new Step(
                    title: 'Exporteer',
                    body: 'Klik op de exportknop boven de tabel en kies csv of xlsx.',
                    topicIds: ['export'],
                ),
                new Step(
                    title: 'Haal het bestand op',
                    body: 'De export draait op de achtergrond. Zodra hij klaar is staat de link in '
                        . 'uw notificatie-overzicht.',
                    topicIds: ['export'],
                ),
            ],
            roles: new TaskRoles(
                performers: [
                    Role::CHIEF_PRIVACY_OFFICER,
                    Role::PRIVACY_OFFICER,
                    Role::DATA_PROTECTION_OFFICIAL,
                ],
                readers: [Role::COUNSELOR, Role::INPUT_PROCESSOR, Role::MANDATE_HOLDER],
            ),
            done: 'Het overzicht staat als bestand klaar in uw notificaties.',
        );
    }

    private static function verwerkingPubliceren(): Task
    {
        return new Task(
            id: 'verwerking-publiceren',
            group: self::GROUP_TERUGVINDEN,
            title: 'Een verwerking publiceren',
            summary: 'Vastgestelde verwerkingen op de openbare website tonen.',
            intro: 'Op de openbare website laat u zien welke verwerkingen uw organisatie '
                . 'uitvoert. Alleen vastgestelde versies van openbare verwerkingen komen daarop '
                . 'terecht.',
            steps: [
                new Step(
                    title: 'Zorg dat er een vastgestelde versie is',
                    body: 'Publiceren gaat altijd over de vastgestelde versie van een verwerking.',
                    topicIds: ['vaststellen'],
                ),
                new Step(
                    title: 'Zet de verwerking op openbaar',
                    body: 'Geef op de detailpagina van de verwerking aan dat deze openbaar is.',
                    topicIds: ['publiceren'],
                ),
                new Step(
                    title: 'Richt de startpagina in',
                    body: 'Onder "Openbare website" stelt u de tekst van de startpagina in.',
                    topicIds: ['publiceren'],
                ),
            ],
            roles: new TaskRoles(
                performers: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL],
            ),
            gate: FeatureGate::PUBLISHING,
            done: 'De verwerking is zichtbaar op de openbare website.',
        );
    }

    private static function gebruikersEnRollenBeheren(): Task
    {
        return new Task(
            id: 'gebruikers-en-rollen-beheren',
            group: self::GROUP_BEHEREN,
            title: 'Gebruikers en rollen beheren',
            summary: 'Iemand toegang geven, of de rollen aanpassen.',
            intro: 'U nodigt een collega uit voor het portaal en bepaalt met welke rollen die '
                . 'binnenkomt. De rol bepaalt wat iemand mag zien en doen.',
            steps: [
                new Step(
                    title: 'Bepaal welke rol nodig is',
                    body: 'Kijk welke rol past bij wat de collega moet kunnen.',
                    topicIds: ['rollen', 'rechten-per-onderdeel'],
                ),
                new Step(
                    title: 'Nodig de gebruiker uit',
                    body: 'Voeg de gebruiker toe boven de gebruikerstabel. De uitnodiging gaat per '
                        . 'e-mail.',
                    topicIds: ['gebruikers'],
                ),
                new Step(
                    title: 'Ken de rollen toe',
                    body: 'Open de gebruiker en stel de rollen in. Alleen een Chief Privacy '
                        . 'Officer kent de rollen Chief Privacy Officer en Mandaathouder toe.',
                    topicIds: ['gebruikers', 'rollen'],
                ),
            ],
            roles: new TaskRoles(
                performers: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                readers: [Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL],
            ),
            done: 'De collega kan inloggen en ziet de onderdelen die bij de toegekende rollen '
                . 'horen.',
        );
    }
}
