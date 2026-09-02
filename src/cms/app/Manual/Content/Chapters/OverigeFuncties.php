<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\FeatureGate;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class OverigeFuncties
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'overige-functies',
            title: 'Overige functies',
            summary: 'Import, export, notificaties, opzoeklijsten en publiceren.',
            topics: [
                self::import(),
                self::export(),
                self::notificaties(),
                self::opzoeklijsten(),
                self::publiceren(),
            ],
        );
    }

    private static function import(): Topic
    {
        return new Topic(
            id: 'import',
            title: 'Import',
            body: <<<'MARKDOWN'
                Met de "import" functionaliteit leest u een bestaand register in. Komt uw
                register uit het [AVG Register
                Rijksoverheid](https://www.avgregisterrijksoverheid.nl/), dan zijn de
                zip-files die dat systeem exporteert direct te importeren.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function export(): Topic
    {
        return new Topic(
            id: 'export',
            title: 'Export',
            body: <<<'MARKDOWN'
                OpenVWR biedt de mogelijkheid om registers te exporteren naar een `.csv` of
                `.xlsx` bestand. De knop voor het exporteren zit boven de overzichtstabel van
                ieder register.

                ![Exporteren](/handleiding/05_overige_functies/01_avg-responsible-processing-records_export.png)

                Is de export voltooid, dan zal er een notificatie getoond worden in het scherm
                rechts bovenin. De links naar de files zijn te vinden in het
                notificatie-overzicht.

                ![Exporteren voltooid](/handleiding/05_overige_functies/02_avg-responsible-processing-records_export_complete.png)
                MARKDOWN,
            roles: [
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
                Role::DATA_PROTECTION_OFFICIAL,
            ],
            availability: '(Chief) Privacy Officer, Functionaris Gegevensbescherming',
        );
    }

    private static function notificaties(): Topic
    {
        return new Topic(
            id: 'notificaties',
            title: 'Notificaties',
            body: <<<'MARKDOWN'
                Het portaal stuurt e-mails op basis van de rollen die u heeft: een Privacy
                Officer krijgt bijvoorbeeld bericht als er een nieuwe versie is aangemaakt, en
                een Chief Privacy Officer als een datalek is gemeld bij de Autoriteit
                Persoonsgegevens. U bepaalt zelf welke van deze e-mails u wilt blijven
                ontvangen.

                Deze instellingen staan onder "Profiel" > "Instellingen", in het blok
                "Notificaties".

                ![Notificatie-instellingen](/handleiding/05_overige_functies/05_profile_settings_notifications.png)

                Alle notificaties staan standaard aan. Vink een notificatie uit om er geen
                e-mail meer over te ontvangen; de wijziging geldt voor al uw organisaties. U
                ziet alleen de notificaties die bij uw eigen rollen horen: een notificatie die
                u toch niet zou ontvangen, wordt niet getoond.

                > **Let op**: Het uitzetten van een notificatie heeft alleen effect op de
                > e-mail. De onderliggende gebeurtenis blijft gewoon zichtbaar in het portaal,
                > bijvoorbeeld in de overzichten van versies en datalekken.
                MARKDOWN,
            availability: 'iedereen die e-mails uit het portaal ontvangt',
        );
    }

    private static function opzoeklijsten(): Topic
    {
        return new Topic(
            id: 'opzoeklijsten',
            title: 'Opzoeklijsten',
            body: <<<'MARKDOWN'
                In het systeem zijn er meerdere velden waar er slechts een keuze mogelijk is
                uit een beperkte set opties. Onder "Opzoeklijsten" zijn deze velden te vinden
                en zijn hun opties aan te passen.

                ![Overzicht van een opzoeklijst](/handleiding/05_overige_functies/03_lookup_list_overview.png)

                In deze opzoeklijsten zijn nieuwe waardes aan te maken, opties in of uit te
                schakelen en opties te verwijderen. Op de detailpagina van een optie is een
                tabel te vinden van alle entiteiten waar deze optie is geselecteerd.

                ![Een waarde in een opzoeklijst bewerken](/handleiding/05_overige_functies/04_lookup_list_edit.png)

                Met de tabs boven de tabel wisselt u tussen ingeschakelde en uitgeschakelde
                waarden. Alleen ingeschakelde waarden verschijnen in de keuzelijsten bij het
                invoeren.

                > **Let op**: Het verwijderen van een optie verwijdert deze compleet uit het
                > systeem! Dit betekent dat overal waar de optie geselecteerd was, nu niets
                > meer geselecteerd is. Als dit niet de bedoeling is, wilt u de optie
                > waarschijnlijk uitschakelen: de optie is dan niet meer te selecteren, maar
                > entiteiten waar deze optie eerder geselecteerd was, blijven ongewijzigd.

                De lijst *Bewaartermijnen* is hierop een uitzondering: wijzigingen daarin
                laten bestaande verwerkingen ongemoeid. Zie
                [Bewaartermijnen](#bewaartermijnen) voor de reden.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function publiceren(): Topic
    {
        return new Topic(
            id: 'publiceren',
            title: 'Publiceren',
            body: <<<'MARKDOWN'
                Vastgestelde verwerkingen kunnen gepubliceerd worden op een openbare website,
                zodat betrokkenen kunnen zien welke verwerkingen uw organisatie uitvoert.

                Onder "Openbare website" in het navigatiemenu stelt u de inhoud van de
                startpagina in. Per verwerking bepaalt u of deze openbaar is: alleen
                vastgestelde versies van openbare verwerkingen komen op de website terecht.

                > **Let op**: Publiceren gaat altijd over de vastgestelde versie. Wijzigt u
                > een verwerking, dan verandert de gepubliceerde tekst pas zodra er een nieuwe
                > versie is vastgesteld.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            gate: FeatureGate::PUBLISHING,
            availability: '(Chief) Privacy Officer',
        );
    }
}
