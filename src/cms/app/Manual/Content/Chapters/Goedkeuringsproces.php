<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class Goedkeuringsproces
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'goedkeuringsproces',
            title: 'Goedkeuringsproces',
            summary: 'Versies indienen, goedkeuren, akkoord geven en vaststellen.',
            topics: [
                self::versiestatussen(),
                self::versieIndienen(),
                self::goedkeuren(),
                self::akkoordGeven(),
                self::vaststellen(),
            ],
        );
    }

    private static function versiestatussen(): Topic
    {
        return new Topic(
            id: 'versiestatussen',
            title: 'Het proces en de statussen',
            body: <<<'MARKDOWN'
                Het portaal ondersteunt het goedkeuringsproces van verwerkingen door:

                - het scheiden van een conceptversie en een workflow voor het vaststellen
                  van een versie;
                - het, indien gewenst, laten akkorderen door Mandaathouders;
                - overzichten van openstaande acties.

                Welke stappen uw organisatie daadwerkelijk doorloopt, en welke rollen daarbij
                betrokken zijn, hangt af van de manier waarop uw organisatie het proces heeft
                ingericht. Werkt uw organisatie bijvoorbeeld niet met Mandaathouders, dan
                slaat u het ophalen van een akkoord over en stelt een Privacy Officer de
                versie direct zelf vast.

                ![Het statusverloop op de detailpagina van een versie](/handleiding/03_goedkeuringsproces/08_snapshots_statusverloop.png)

                Een versie heeft altijd een status uit de volgende lijst:

                1. `status:concept:Concept`: dit is een actieve werkversie, en deze is nog
                   niet ingediend.
                2. `status:review:In Review`: deze versie is ingediend en moet nog
                   beoordeeld worden door een Privacy Officer.
                3. `status:approved:Goedgekeurd`: deze versie is goedgekeurd door een Privacy
                   Officer en kan worden vastgesteld, eventueel nadat Mandaathouders akkoord
                   hebben gegeven.
                4. `status:established:Vastgesteld`: deze versie is vastgesteld en geldt
                   daarmee als de geldende versie.
                5. `status:expired:Vervallen`: deze versie is komen te vervallen, mogelijk
                   omdat een nieuwere versie is aangemaakt die dezelfde status heeft
                   verkregen.

                ![De knop "Status aanpassen" op de detailpagina van een versie](/handleiding/03_goedkeuringsproces/09_snapshots_status_aanpassen.png)

                Een (Chief) Privacy Officer kan de status met de knop
                "Status aanpassen" op de detailpagina van een versie rechtstreeks zetten,
                bijvoorbeeld om een versie te laten vervallen die toch niet vastgesteld gaat
                worden. Elke statuswijziging wordt vastgelegd en is terug te zien onder
                "Statuswijzigingen".
                MARKDOWN,
        );
    }

    private static function versieIndienen(): Topic
    {
        return new Topic(
            id: 'versie-indienen',
            title: 'Versie indienen en Mandaathouders koppelen',
            body: <<<'MARKDOWN'
                Voor alle entiteiten in de registers en voor alle gerelateerde entiteiten
                worden versies bijgehouden. U hoeft daar niets voor te doen: zodra u een
                entiteit opslaat, legt het portaal die inhoud vast als versie met de status
                "Concept". Slaat u daarna opnieuw op, dan wordt diezelfde conceptversie
                bijgewerkt.

                Een versie is te vinden onderaan de pagina bij de tabellen op het eerste
                tabblad "Versies":

                ![Versie selecteren](/handleiding/03_goedkeuringsproces/02_avg-responsible-processing-records_edit_versie_select.png)

                Is de entiteit klaar voor het goedkeuringsproces, dan dient u de
                conceptversie in met de knop "Start vaststellen", rechtsbovenin op de
                bewerkpagina van de entiteit zelf.

                ![De knop "Start vaststellen" rechtsbovenin de bewerkpagina](/handleiding/03_goedkeuringsproces/01_avg-responsible-processing-records_edit_versie.png)

                Die knop slaat eerst op en stuurt de conceptversie daarna naar review; u
                hoeft dus niet apart op te slaan.

                Op dat moment worden de verplichte velden gecontroleerd. Ontbreekt er nog
                iets, dan blijft de versie een concept en verschijnt de melding bij het veld
                zelf wat nog informatie nodig heeft. Wanneer alle verplichte velden in het
                formulier zijn ingevuld drukt u opnieuw op "Start vaststellen".

                ### Twee meldingen die u kunt tegenkomen

                Meestal gaat de versie meteen naar review. In twee gevallen krijgt u eerst
                een melding.

                *"Er loopt al een vaststelling"* betekent dat er nog een versie in het
                goedkeuringsproces zit, bijvoorbeeld met de status "In review" of
                "Goedgekeurd". Gaat u door, dan vervalt die lopende versie. De nieuwe versie
                wordt dan klaargezet als review. Kies "Toch doorgaan" als dat de bedoeling
                is; twijfelt u, laat de lopende versie dan eerst afronden.

                *"Geen wijzigingen"* betekent dat de huidige versie niet verschilt van de
                vorige versie (er zijn geen aanpassingen). Daarmee is het niet nodig om een
                nieuwe versie in te dienen.

                Zodra een versie is ingediend staat de
                inhoud vast en kunt u hem openen. Een klik op de versie zal de detailpagina
                van deze versie tonen. Hier kunnen Mandaathouders worden toegevoegd aan een
                versie door op "Ondertekeningen" te klikken:

                ![Ondertekeningen selecteren](/handleiding/03_goedkeuringsproces/03_snapshots_ondertekeningen.png)

                De knop "Mandaathouders toevoegen" toont een lijst met Mandaathouders: deze
                zijn te selecteren en kunnen worden toegevoegd met de knop "Toevoegen".

                ![Mandaathouder toevoegen](/handleiding/03_goedkeuringsproces/04_snapshots_mandaathouder.png)

                > **Hint**: Gebruikers met de rol Privacy Officer krijgen automatisch een
                > e-mail als er een versie naar review is gestuurd. Wilt u die e-mails niet
                > ontvangen, dan zet u ze uit op
                > uw eigen profielpagina (zie [Notificaties](#notificaties)). Wilt u één
                > specifieke versie onder de aandacht brengen, gebruik dan de
                > "Ondertekeningen": dat legt het verzoek vast in het portaal in plaats van
                > alleen in iemands mailbox.

                > **Let op**: Is een versie eenmaal ingediend, dan is de inhoud van deze
                > versie niet meer aanpasbaar: slechts de status van een versie kan nog
                > aangepast worden door een Privacy Officer. Aanpassingen maken gaat door via
                > het systeem een nieuw concept te maken. En vervolgens kan deze ingediend
                > worden voor review en daarna worden goedgekeurd.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer',
        );
    }

    private static function goedkeuren(): Topic
    {
        return new Topic(
            id: 'goedkeuren',
            title: 'Goedkeuren',
            body: <<<'MARKDOWN'
                Het goedkeuren van een versie kan op de detailpagina van de desbetreffende
                versie. Een Privacy Officer keurt een versie goed als deze correct is
                opgesteld en er op korte termijn geen nieuwe versies verwacht worden. Werkt
                uw organisatie met Mandaathouders, dan betekent goedkeuring bovendien dat de
                versie in die context aan hen mag worden aangeboden voor een akkoord.

                Er is een overzicht van alle versies te vinden in het navigatiemenu links.
                Deze tabel geeft een overzicht van alle versies en is te sorteren en filteren
                op *Entiteit-type*, *Naam versie*, *Versienummer* en *Status*. De filter kan
                ingesteld worden rechtsboven in de tabel.

                ![Versie overzicht](/handleiding/03_goedkeuringsproces/05_organisation-snapshots.png)

                > **Hint**: Dit overzicht kan gebruikt worden als To Do lijst. Filter op alle
                > versies die de status "In review" hebben: dit geeft een overzicht van alle
                > nieuw aangemaakte versies waarop nog een goedkeuring wordt verwacht.

                Is eenmaal een versie geselecteerd, dan kan een versie met de status "In
                review" goedgekeurd worden met de knop "Goedkeuren" rechtsbovenin het scherm.
                Dit geeft aan dat deze versie is goedgekeurd door een Privacy Officer en kan
                worden vastgesteld, eventueel eerst nog ter akkoord aan Mandaathouders.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function akkoordGeven(): Topic
    {
        return new Topic(
            id: 'akkoord-geven',
            title: 'Akkoord geven',
            body: <<<'MARKDOWN'
                Deze stap is alleen aan de orde als uw organisatie met Mandaathouders werkt en
                er Mandaathouders aan de versie zijn gekoppeld.

                ![Akkoord geven](/handleiding/03_goedkeuringsproces/07_personal-snapshot-approvals_akkoord_geven.png)

                Een Mandaathouder kan akkoord geven op een versie door op de versie
                detailpagina onderaan op "Akkoord" te klikken. De Mandaathouder kan hier ook
                op "Niet akkoord" drukken, wat de mogelijkheid biedt om een notitie achter te
                laten.

                > **Hint**: Mandaathouders kunnen op hun profielpagina hun voorkeuren aangeven
                > voor email notificaties. Het is mogelijk om een notificatie te krijgen bij
                > iedere versie waarvoor de Mandaathouder is uitgenodigd en welke is
                > goedgekeurd door een Privacy Officer. Dit kan een hoop emails opleveren
                > indien er veel versies tegelijk ter akkoord worden aangeboden: het is
                > daarom ook mogelijk om wekelijks 1 enkel overzicht te krijgen van alle
                > versies waarvoor een akkoord gewenst is.
                MARKDOWN,
            roles: [Role::MANDATE_HOLDER],
            availability: 'Mandaathouder',
        );
    }

    private static function vaststellen(): Topic
    {
        return new Topic(
            id: 'vaststellen',
            title: 'Vaststellen',
            body: <<<'MARKDOWN'
                Deze stap is identiek aan [Goedkeuren](#goedkeuren), met als enige verschil
                dat het hier "Vaststellen" betreft.

                > **Hint**: Ook hier kan het overzicht gebruikt worden als To Do lijst. Filter
                > op alle versies die de status "Goedgekeurd" hebben: kijk in de tabel welke
                > versies vastgesteld kunnen worden, en - als u met Mandaathouders werkt - of
                > er al genoeg ondertekeningen zijn.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }
}
