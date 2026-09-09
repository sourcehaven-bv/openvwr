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
            body: self::importBestandEnMapping() . self::importProefdraaienEnImporteren() . self::importKoppelingen(),
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function importBestandEnMapping(): string
    {
        return <<<'MARKDOWN'
                Onder "Import" leest u gegevens in vanuit een bestand. Er is één scherm voor
                twee soorten bestanden; OpenVWR herkent zelf welke u aanbiedt.

                **Een OpenVWR-export (`.zip`)** - bijvoorbeeld uit het [AVG Register
                Rijksoverheid](https://www.avgregisterrijksoverheid.nl/) of uit een andere
                OpenVWR-omgeving. De indeling is al bekend, dus er valt niets te koppelen: het
                scherm toont welke registers in het bestand zitten en hoeveel records, en u
                bevestigt.

                **Een Excel- of CSV-bestand** - uit een ander systeem, met eigen kolomnamen.
                Hiervoor koppelt u de kolommen aan de velden in OpenVWR. De rest van dit
                onderwerp beschrijft die stappen.

                ### Bestand kiezen

                Kies eerst het register waarin de gegevens terecht moeten komen en upload
                daarna het bestand. De eerste rij moet de kolomnamen bevatten. Zodra het
                bestand is geüpload wordt het meteen geanalyseerd; een aparte knop is niet
                nodig.

                Het register hoeft u alleen te kiezen bij een Excel- of CSV-bestand. Biedt u
                een OpenVWR-export aan, dan bepaalt het bestand dat zelf.

                ![Bestand kiezen](/handleiding/05_overige_functies/06_import_upload.png)

                ### Mapping controleren

                Bovenaan staat voor welk register de kolommen zijn gekoppeld. Is het
                bestand voor een ander register bedoeld, kies dat dan daar; de mapping
                wordt opnieuw voorgesteld.

                OpenVWR probeert iedere kolom zelf aan een veld te koppelen. Dat gebeurt op
                basis van de kolomnaam én de waarden in de kolom: een kolom met "ja" en "nee"
                hoort bij een ja/nee-veld, ook als de naam op een datumveld lijkt. Per kolom
                staat wat het systeem heeft gedaan:

                | Melding | Betekenis |
                | --- | --- |
                | Automatisch ingevuld | Het systeem is zeker van de koppeling |
                | Voorstel - controleer | Waarschijnlijk goed, maar het is de moeite van het nakijken waard |
                | Nog geen keuze gemaakt | Er is geen betrouwbare koppeling gevonden; kies zelf een veld |
                | Zelf gekozen | U heeft deze koppeling aangepast |

                Twijfelt het systeem tussen twee velden die ongeveer even goed passen, dan
                doet het bewust geen voorstel: een verkeerde suggestie is lastiger te
                herkennen dan een lege.

                Onder iedere kolomnaam staan enkele waarden uit het bestand, zodat u kunt
                zien wat er werkelijk in de kolom staat. Kolommen die niet mee moeten -
                bijvoorbeeld de naam van de melder of de afdeling - laat u op "niet
                importeren" staan.

                ![Mapping controleren](/handleiding/05_overige_functies/07_import_mapping.png)

                Onder het gekozen veld staat hoe de waarde gelezen wordt: als tekst, datum,
                ja/nee of lijst. Dat is geen keuze maar een gevolg van het veld dat u kiest -
                een datumveld leest altijd een datum.

                Kopcellen uit sjablonen die een invulinstructie bevatten ("Omschrijving -
                noteer hier de naam", "Grondslag, meerdere keuzes mogelijk") worden
                teruggebracht tot de naam van de kolom.

                ### Datums

                Een datumkolom wordt in één formaat gelezen, voor de hele kolom.
                Meestal blijkt dat formaat uit de waarden zelf en staat het onder de
                kolom: "13-03-2026 wordt gelezen als 13 maart 2026". Kan een waarde
                twee kanten op, zoals "04-03-2026", dan vraagt het scherm welke datum
                dat is. Zonder antwoord wordt er niet proefgedraaid of geïmporteerd:
                raden is precies wat hier niet mag gebeuren. De keuze wordt met de
                mapping bewaard.

                ![Welke datum is dat?](/handleiding/05_overige_functies/08_import_datumformaat.png)

                ### Ja/nee omzetten naar een datum

                Soms registreert het bronsysteem alleen *dát* iets is gemeld, terwijl OpenVWR
                ook wil weten *wanneer*. Koppelt u een ja/nee-kolom aan een datumveld, dan
                vraagt het scherm welke datum bij "ja" hoort: de datum van de import, of een
                datum die u zelf kiest. Bij "nee" blijft het veld leeg.

                MARKDOWN;
    }

    private static function importProefdraaienEnImporteren(): string
    {
        return <<<'MARKDOWN'
                ### Proefdraaien

                Met "Proefdraaien" controleert OpenVWR de mapping tegen alle rijen zonder
                iets op te slaan. U ziet hoeveel rijen goed gaan en welke aandacht nodig
                hebben, met per rij de reden - bijvoorbeeld een verplicht veld dat leeg
                blijft, een waarde die niet als datum gelezen kan worden, of een tekst die
                langer is dan het veld toelaat. Pas de mapping aan en draai opnieuw proef
                tot het beeld klopt. Is hetzelfde veld voor twee kolommen gekozen, dan meldt
                het scherm dat eerst: een veld neemt één kolom.

                ![Resultaat van een proefdraai](/handleiding/05_overige_functies/09_import_proefdraai.png)

                Velden die het register verplicht stelt maar die in het bestand ontbreken,
                beginnen zoals op het invoerformulier: een ja/nee-veld als "nee", een vaste
                keuze als de eerste optie. Andere ontbrekende velden blijven leeg.

                ### Importeren

                "Importeren" voegt de rijen toe die passen. Rijen met een probleem worden
                overgeslagen; die kunt u in het bronbestand corrigeren en opnieuw aanbieden.
                Kan een rij ondanks de proefdraai toch niet worden opgeslagen, dan telt de
                kop van het resultaat die mee en staat de rij onder "Niet opgeslagen" met
                de reden.
                Vult u een naam in bij "Mapping bewaren voor hergebruik", dan wordt de
                mapping opgeslagen. Biedt u later een bestand met dezelfde kolommen aan, dan
                herkent OpenVWR de indeling en is de mapping al ingevuld.

                Koppelt u een kolom aan "Bronkenmerk", bijvoorbeeld het meldnummer uit het
                bronsysteem, dan herkent OpenVWR de rij bij een volgende import aan dat
                kenmerk en ontstaan er geen dubbelen. Zonder bronkenmerk maakt een tweede
                import van hetzelfde bestand de records opnieuw aan.

                > **Let op**: Geïmporteerde records komen als gewoon bronrecord binnen. Ze
                > hebben nog geen versie en doorlopen het goedkeuringsproces pas als u er
                > een aanmaakt.

                MARKDOWN;
    }

    private static function importKoppelingen(): string
    {
        return <<<'MARKDOWN'
                ### Koppelingen naar verwerkers en systemen

                Een kolom kan ook naar een gekoppeld record verwijzen, zoals een verwerker of
                systeem. Die staan in de keuzelijst onder **Koppelingen**. OpenVWR zoekt de
                naam op in het register en gebruikt het bestaande record; bestaat het nog
                niet, dan wordt het aangemaakt. Staan er meerdere namen in één cel, zet ze
                dan onder elkaar in die cel, of gescheiden door een komma en een spatie
                zoals de export van OpenVWR ze schrijft.

                Hoort er meer bij dan een naam, dan kan dat uit aparte kolommen komen. Voor
                een verwerker biedt de lijst bijvoorbeeld ook "Verwerkers - E-mail" en
                "Verwerkers - Postcode". Die kolommen horen bij dezelfde verwerker; staan er
                meerdere namen in één cel, dan wordt op volgorde gekoppeld - de tweede naam
                krijgt het tweede e-mailadres.

                Verschillen in schrijfwijze worden opgevangen: hoofdletters, dubbele spaties
                en rechtsvormen als "B.V." leiden tot hetzelfde record. Namen die alleen op
                elkaar *lijken* worden niet samengevoegd - "Zorggroep Noord" en "Zorggroep
                Oost" blijven gescheiden.

                Na afloop toont het scherm welke records nieuw zijn aangemaakt en welke op
                een afwijkende schrijfwijze zijn gekoppeld. Loop die lijst na: zo voorkomt u
                dat dezelfde verwerker onder twee namen in het register komt.

                ![Na de import: wat is aangemaakt en gekoppeld](/handleiding/05_overige_functies/10_import_resultaat.png)

                Bestaan er al meerdere records met dezelfde naam, dan koppelt OpenVWR aan het
                oudste en meldt dit. Voeg die dubbelen samen, want de koppeling wijst dan
                mogelijk naar het verkeerde record.

                Voor verwerkingen geldt een uitzondering: die worden **niet** automatisch
                aangemaakt. Verwijst een datalek naar een verwerking die niet bestaat, dan
                wordt dat na afloop gemeld en legt u de koppeling zelf. Zo groeit het register
                niet ongemerkt met lege verwerkingen.

                ### Notities

                Een kolom die nergens in past - een status uit het oude systeem, een
                afdeling, een vrij tekstveld - hoeft niet verloren te gaan. Kies
                **Notitie**: de waarde komt als opmerking bij het record te staan, met de
                kolomnaam ervoor. Heet de kolom zelf "Opmerkingen", "Notities" of "Tekst",
                dan zijn het al notities en blijven ze zoals ze zijn. Meerdere kolommen
                mogen naar Notitie; elke kolom wordt een eigen opmerking en een lege cel
                geeft er geen. De keuze bestaat alleen bij registers die opmerkingen
                kennen, zoals de verwerkingen en DPIA's. De opmerking van de FG heeft een
                eigen doel, "Opmerking FG", dat alleen wie de FG-opmerkingen mag lezen te
                zien krijgt; de export bevat de FG-opmerking nooit.

                ### Eén record over meerdere rijen

                Sommige registertools zetten één record over meerdere rijen: de kolommen
                van het record staan op iedere rij, en elke rij bevat één systeem, één
                doel of één notitie. OpenVWR herkent dat en stelt bovenaan het scherm de
                kolom voor die het record aanduidt, meestal een nummer of id. Rijen met
                dezelfde waarde in die kolom worden dan één record: koppelingen en notities
                worden uit alle rijen verzameld, een gewoon veld moet in die rijen dezelfde
                waarde hebben. Klopt de kolom niet, kies dan een andere, of kies "iedere rij
                is een eigen record". De keuze gaat mee in een bewaard profiel.

                ### Eigen export opnieuw inlezen

                De Excel-export van een register (de exportknop boven de tabel) kunt u zo
                weer inlezen: de kolomnamen zijn dezelfde als in de keuzelijst, dus de
                mapping wordt vrijwel volledig automatisch ingevuld en koppelingen komen
                bij de bestaande records terecht. Zo zet u records over naar een andere
                organisatie, of vult u ze in Excel aan en leest u ze opnieuw in.

                ### Opzoeklijsten

                Velden die uit een opzoeklijst komen, zoals de dienst waar een verwerking bij
                hoort, staan in de keuzelijst onder **Opzoeklijsten**. Komt een waarde nog
                niet in de lijst voor, dan wordt hij toegevoegd - dat is bij deze lijsten
                juist de bedoeling.
                MARKDOWN;
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
                zodat betrokkenen kunnen zien welke verwerkingen uw organisatie uitvoert. Voor
                veel organisaties is dat een wettelijke verplichting; voor alle organisaties
                is het de meest zichtbare vorm van verantwoording over de registratie.

                ### Wanneer komt een verwerking op de website

                Er moeten twee dingen kloppen. Er moet een *vastgestelde* versie zijn, en de
                verwerking moet *openbaar* zijn. Ontbreekt een van beide, dan gebeurt er
                niets.

                Dat u een verwerking openbaar maakt, legt u vast met het veld "Publiceer
                vanaf" op de detailpagina. Vult u een datum in de toekomst in, dan verschijnt
                de verwerking pas vanaf die datum; met de knop "Publiceer vanaf nu" zet u hem
                direct open. Laat u het veld leeg, dan blijft de verwerking binnen het portaal
                en komt hij niet op de website.

                In het blok "Publieke beschikbaarheid" op de detailpagina ziet u de huidige
                status - publiek beschikbaar of niet - en een overzicht van de periodes waarin
                de verwerking publiek is geweest. Dat overzicht blijft staan, ook nadat u een
                verwerking weer van de website haalt: het laat zien wat er wanneer openbaar
                was.

                ### Een verwerking weer van de website halen

                Maakt u het veld "Publiceer vanaf" leeg, dan verdwijnt de verwerking bij de
                eerstvolgende bouw van de website. De historie van de publieke beschikbaarheid
                blijft bewaard.

                > **Let op**: Publiceren gaat altijd over de vastgestelde versie. Wijzigt u
                > een verwerking, dan verandert de gepubliceerde tekst pas zodra er een nieuwe
                > versie is vastgesteld. Een correctie op een gepubliceerde tekst vraagt dus
                > om een nieuwe versie die het hele goedkeuringsproces doorloopt.

                > **Let op**: Wat u publiceert is openbaar en kan door zoekmachines worden
                > opgenomen. Loop voor het publiceren na of er geen gegevens in staan die niet
                > naar buiten horen; een verwerking terughalen wist niet wat anderen al hebben
                > gezien of bewaard.

                ### De website zelf

                De inrichting van de openbare website - de opbouw van de paginaboom en de
                teksten eromheen - staat onder "Functioneel beheer" en is werk van een
                Functioneel beheerder. Als (Chief) Privacy Officer bepaalt u wélke
                verwerkingen erop komen; niet hoe de website eruitziet. Zie
                [De openbare website inrichten](#websitebeheer) voor die kant van het werk.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            gate: FeatureGate::PUBLISHING,
            availability: '(Chief) Privacy Officer',
        );
    }
}
