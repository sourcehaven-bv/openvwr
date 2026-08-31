<?php

declare(strict_types=1);

namespace App\Manual\Content;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\FeatureGate;
use App\Manual\Topic;

/**
 * The reference layer: the canonical text of the manual.
 *
 * This is the single source of truth. Every explanation is written exactly
 * once, here, and the tasks in TaskContent link to it. Migrated from the seven
 * markdown chapters that used to be built into a pdf; the LaTeX constructs of
 * that build (\label, \ref, \textcolor, \newpage) have become anchors,
 * markdown links, styled status markers and topic boundaries.
 */
final class ReferenceContent
{
    /**
     * @return array<Chapter>
     */
    public static function chapters(): array
    {
        return [
            self::welkom(),
            self::registers(),
            self::goedkeuringsproces(),
            self::beheer(),
            self::overigeFuncties(),
            self::labels(),
            self::rollenEnRechten(),
        ];
    }

    private static function welkom(): Chapter
    {
        return new Chapter(
            id: 'welkom',
            title: 'Welkom',
            summary: 'Wat OpenVWR is, en hoe u inlogt met een authenticator.',
            topics: [
                new Topic(
                    id: 'over-openvwr',
                    title: 'Over OpenVWR',
                    body: <<<'MARKDOWN'
                        OpenVWR is het centrale platform waarmee organisaties alle verwerkingen van
                        persoonsgegevens kunnen bijhouden, laten goedkeuren en publiceren.

                        OpenVWR is een webapplicatie met formulieren die een eenduidige en
                        gestructureerde invulling van het verwerkingsregister faciliteert. De
                        belangrijkste functionaliteiten:

                        - Toegankelijk voor medewerkers van de gehele organisatie, inclusief
                          onderliggende organisatieonderdelen.
                        - Importfunctionaliteit voor bestaande registers, met de mogelijkheid
                          geïmporteerde gegevens handmatig aan te vullen.
                        - Koppeling van documenten aan een verwerking, waarbij deze documenten ook in
                          het systeem opgeslagen worden.
                        - Een administratieportaal voor gebruikers en daaraan gerelateerde rollen en
                          rechten.
                        - Online waarschuwingen bij (binnenkort) verlopen documenttermijnen,
                          bijvoorbeeld van DPIA's.
                        - Registratie van algoritmes, als een bijzondere vorm van verwerkingen.
                        - Een datalekregister waar Privacy Officers datalekken kunnen registreren,
                          exporteren en eventueel kunnen melden bij de Chief Privacy Officer.
                        - Vastlegging van relaties tussen entiteiten en (sub)verwerkingen, met
                          geautomatiseerde voorgestelde veranderingen.
                        - Alerting, bijvoorbeeld een email wanneer een document zijn geldigheid gaat
                          verliezen of wanneer een taak voor een gebruiker klaar staat.

                        Generieke informatie omtrent OpenVWR:

                        - website: [https://openvwr.nl/](https://openvwr.nl/)
                        - codebase: PHP, Laravel, Filament
                        - helpdesk: neem contact op met uw Chief Privacy Officer
                        MARKDOWN,
                ),
                new Topic(
                    id: 'inloggen',
                    title: 'Inloggen',
                    body: <<<'MARKDOWN'
                        ![Login pagina](/handleiding/01_welkom/01_login.png)

                        Voor toegang tot OpenVWR zult u moeten beschikken over een account en een
                        authenticator applicatie.

                        ### Een account op OpenVWR

                        Voordat u in kunt loggen heeft u een account nodig: neem hiervoor contact op
                        met uw Chief Privacy Officer.

                        ### Een authenticator applicatie

                        De applicatie is beschermd met 2 Factor Authentication, ook wel bekend als One
                        Time Password protection. Hiervoor heeft u een van de volgende apps nodig op
                        uw mobiele device:

                        1. Microsoft Authenticator:
                           [Android App](https://play.google.com/store/apps/details?id=com.azure.authenticator&hl=nl&gl=US) /
                           [iPhone App](https://apps.apple.com/nl/app/microsoft-authenticator/id983156458)
                        2. Google Authenticator:
                           [Android App](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) /
                           [iPhone App](https://apps.apple.com/nl/app/google-authenticator/id388497605)
                        3. FreeOTP Authenticator: [Website](https://freeotp.github.io/)
                        MARKDOWN,
                ),
                new Topic(
                    id: 'authenticator-instellen',
                    title: 'De authenticator instellen',
                    body: <<<'MARKDOWN'
                        Tweefactorauthenticatie is verplicht. Zolang u deze nog niet heeft ingesteld,
                        brengt OpenVWR u na het inloggen automatisch naar de pagina "Mijn profiel". U
                        kunt de rest van de applicatie pas gebruiken nadat u deze stap heeft
                        afgerond; het is dus niet mogelijk deze pagina over te slaan.

                        ![De authenticator instellen](/handleiding/01_welkom/02_profile_one_time_password.png)

                        Op deze pagina stelt u de authenticator als volgt in:

                        1. Klik op "Inschakelen". Er verschijnt een QR-code met daaronder een sleutel.
                        2. Scan de QR-code met de authenticator applicatie op uw mobiele device. Kan
                           uw device geen QR-code scannen, dan voert u de getoonde sleutel handmatig
                           in de applicatie in.
                        3. Klik op "Bevestigen" en vul de code van zes cijfers in die uw authenticator
                           applicatie toont.

                        Met de knop "Resetten" begint u de instelling opnieuw; er wordt dan een nieuwe
                        QR-code met een nieuwe sleutel aangemaakt.

                        Na een geldige code is de tweefactorauthenticatie ingeschakeld en heeft u
                        toegang tot de applicatie. Voert u een onjuiste code in, dan meldt de
                        applicatie dat en kunt u het opnieuw proberen.

                        ### Inloggen met de authenticator

                        Zodra de authenticator is ingesteld, verloopt het inloggen bij iedere volgende
                        sessie in twee stappen. Eerst logt u in met uw e-mailadres, waarna u een
                        tweefactorscherm krijgt waarin u de actuele code van zes cijfers uit uw
                        authenticator applicatie invult. De code wisselt periodiek: neem daarom altijd
                        de code over die op dat moment in de applicatie zichtbaar is.

                        Beschikt u niet meer over uw authenticator applicatie, bijvoorbeeld na de
                        aanschaf van een nieuw mobiel device, neem dan contact op met uw Chief Privacy
                        Officer of een beheerder. Deze kan uw tweefactorauthenticatie resetten, waarna
                        u bij de eerstvolgende keer inloggen opnieuw de stappen hierboven doorloopt.
                        MARKDOWN,
                ),
            ],
        );
    }

    private static function registers(): Chapter
    {
        return new Chapter(
            id: 'registers',
            title: 'Registers',
            summary: 'De verwerkingsregisters, algoritmes en datalekken.',
            topics: [
                new Topic(
                    id: 'verwerkingsregisters',
                    title: 'Verwerkingsregisters',
                    body: <<<'MARKDOWN'
                        ![Registers](/handleiding/02_registers/01_avg-responsible-processing-records.png)

                        Na het inloggen komt u in OpenVWR en ziet u links in het scherm het
                        navigatiemenu. Bovenaan staan de registers: AVG Verantwoordelijke
                        Verwerkingen en AVG Verwerker Verwerkingen.

                        In deze registers kunnen verwerkingen toegevoegd worden. Na het klikken op een
                        register verschijnt er een overzicht van de verwerkingen in het register met
                        de volgende kolommen waarop u de tabel kunt sorteren:

                        - *Nummer verwerking*: het unieke nummer van de verwerking;
                        - *Naam verwerking*: de naam van de verwerking;
                        - *Status*: de status van de versies van de verwerking;
                        - *Periodieke review*: de datum waarop de verwerking opnieuw een review moet
                          ontvangen.

                        Als u op een verwerking klikt, of op de knop "Verwerking aanmaken", komt u op
                        het bijbehorende invoerformulier, ook wel de detailpagina genoemd.

                        ![Verwerking detailpagina](/handleiding/02_registers/02_avg-responsible-processing-records_edit.png)

                        Op deze pagina zijn alle gegevens van een verwerking in te voeren en kunnen
                        relaties gelegd worden tussen andere verwerkingen. Aan de rechterkant is een
                        navigatiemenu voor de verschillende domeinen van gegevensinvoer.

                        > **Hint**: U kunt een verwerking opslaan zonder dat alle informatie is
                        > ingevoerd. Verplichte velden worden pas gecontroleerd op het moment dat u
                        > een versie aanmaakt; ontbreekt er dan nog iets, dan ziet u welke velden dat
                        > zijn en bij welke stap ze horen. Als een verwerking eenmaal klaar is voor
                        > het goedkeuringsproces kunt u een versie aanmaken die via dat proces
                        > vastgesteld kan worden, afhankelijk van de inrichting bij uw organisatie
                        > eventueel nadat Mandaathouders akkoord hebben gegeven. Voor meer
                        > informatie: zie [Versie aanmaken](#versie-aanmaken).

                        Eenmaal opgeslagen in het systeem zullen relaties met andere entiteiten
                        zichtbaar zijn in de tabellen onderaan in het scherm. Hiermee kunt u snel
                        navigeren naar de gerelateerde entiteiten.

                        > **Hint**: Het is mogelijk om verwerkingen te dupliceren met de knop
                        > rechtsbovenin: er wordt een nieuwe verwerking aangemaakt met precies
                        > dezelfde waardes voor alle velden. Dit maakt het makkelijk om meerdere
                        > verwerkingen in te voeren met grotendeels dezelfde eigenschappen: u hoeft
                        > alleen maar de velden te wijzigen waar de verwerkingen niet overeenkomen.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                        Role::COUNSELOR,
                        Role::DATA_PROTECTION_OFFICIAL,
                        Role::MANDATE_HOLDER,
                    ],
                    availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                        . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
                ),
                new Topic(
                    id: 'wpg-register',
                    title: 'WPG Verantwoordelijke Verwerkingen',
                    body: <<<'MARKDOWN'
                        Naast de AVG-registers kent OpenVWR het register WPG Verantwoordelijke
                        Verwerkingen, voor verwerkingen die onder de Wet politiegegevens vallen.

                        Het register werkt op identieke wijze als de AVG-verwerkingsregisters: u ziet
                        dezelfde kolommen in het overzicht, en op de detailpagina legt u de gegevens
                        van de verwerking op dezelfde manier vast. Ook het goedkeuringsproces, labels,
                        import en export werken er hetzelfde.

                        Bij het WPG-register hoort een eigen opzoeklijst, die u beheert zoals de
                        andere opzoeklijsten.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                        Role::COUNSELOR,
                        Role::DATA_PROTECTION_OFFICIAL,
                        Role::MANDATE_HOLDER,
                    ],
                    gate: FeatureGate::WPG,
                    availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                        . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
                ),
                new Topic(
                    id: 'algoritmes',
                    title: 'Algoritmes',
                    body: <<<'MARKDOWN'
                        Het algoritmeregister werkt op identieke wijze als de verwerkingregisters. In
                        de overzichtstabel zijn dezelfde kolommen zichtbaar omdat ze deze
                        eigenschappen delen met de verwerkingen.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                        Role::COUNSELOR,
                        Role::DATA_PROTECTION_OFFICIAL,
                        Role::MANDATE_HOLDER,
                    ],
                    availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                        . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
                ),
                new Topic(
                    id: 'datalekken',
                    title: 'Datalekken',
                    body: <<<'MARKDOWN'
                        In dit register kunnen datalekken toegevoegd worden. Na het klikken op het
                        datalekregister verschijnt er een overzicht van alle datalekken in het
                        register met de volgende kolommen waarop u de tabel kunt sorteren:

                        - *Nummer datalek*: het unieke nummer van de datalek;
                        - *Naam datalek*: de naam van de datalek;
                        - *Datum melding*: de datum van melding;
                        - *Gemeld aan de autoriteit persoonsgegevens (AP)*: een indicatie of het lek
                          al dan niet gemeld is bij de AP.

                        Als u op een datalek klikt, of op de knop "Datalek aanmaken", komt u op het
                        bijbehorende invoerformulier, ook wel de detailpagina genoemd.

                        > **Hint**: U kunt op de detailpagina aangeven of een datalek gemeld is bij de
                        > autoriteit persoonsgegevens (AP). Indien u aangeeft dat u dat gedaan heeft
                        > en de datalek opslaat, dan krijgen de Chief Privacy Officer(s) en
                        > Mandaathouder(s) van de organisatie automatisch een email met daarin een
                        > link naar deze datalek in het portaal.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR_DATABREACH,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                        Role::COUNSELOR,
                        Role::DATA_PROTECTION_OFFICIAL,
                    ],
                    availability: 'Invoerder Datalekken, (Chief) Privacy Officer, Raadpleger (lezen), '
                        . 'Functionaris Gegevensbescherming (lezen)',
                ),
            ],
        );
    }

    private static function goedkeuringsproces(): Chapter
    {
        return new Chapter(
            id: 'goedkeuringsproces',
            title: 'Goedkeuringsproces',
            summary: 'Versies aanmaken, goedkeuren, akkoord geven en vaststellen.',
            topics: [
                new Topic(
                    id: 'versiestatussen',
                    title: 'Het proces en de statussen',
                    body: <<<'MARKDOWN'
                        Het portaal ondersteunt het goedkeuringsproces van verwerkingen middels
                        overzichten en automatisering:

                        - het aanmaken van versies en het aanpassen van de status van een versie;
                        - het eventueel ophalen van een akkoord bij Mandaathouders op een goedgekeurde
                          versie;
                        - het vaststellen van versies;
                        - overzichten van openstaande acties.

                        Welke stappen uw organisatie daadwerkelijk doorloopt, en welke rollen daarbij
                        betrokken zijn, hangt af van de manier waarop uw organisatie het proces heeft
                        ingericht. Werkt uw organisatie bijvoorbeeld niet met Mandaathouders, dan
                        slaat u het ophalen van een akkoord over en stelt een Privacy Officer de
                        versie zelf vast.

                        Een versie heeft altijd een status uit de volgende lijst:

                        1. `status:review:In Review`: deze versie is ingediend en moet nog
                           beoordeeld worden door een Privacy Officer.
                        2. `status:approved:Goedgekeurd`: deze versie is goedgekeurd door een Privacy
                           Officer en kan worden vastgesteld, eventueel nadat Mandaathouders akkoord
                           hebben gegeven.
                        3. `status:established:Vastgesteld`: deze versie is vastgesteld en geldt
                           daarmee als de geldende versie.
                        4. `status:expired:Vervallen`: deze versie is komen te vervallen, mogelijk
                           omdat een nieuwere versie is aangemaakt die dezelfde status heeft
                           verkregen.
                        MARKDOWN,
                ),
                new Topic(
                    id: 'versie-aanmaken',
                    title: 'Versie aanmaken en Mandaathouders koppelen',
                    body: <<<'MARKDOWN'
                        Voor alle entiteiten in de registers en voor alle gerelateerde entiteiten is
                        het mogelijk om een versie aan te maken.

                        Een nieuwe versie kan aangemaakt worden door een entiteit te openen en op de
                        knop "Versie aanmaken" te klikken (rechtsbovenin):

                        ![Versie aanmaken](/handleiding/03_goedkeuringsproces/01_avg-responsible-processing-records_edit_versie.png)

                        Een (nieuwe) versie is te vinden onderaan de pagina bij de tabellen op het
                        eerste tabblad "Versies". De nieuw aangemaakte versie zal de status "In
                        review" hebben:

                        ![Versie selecteren](/handleiding/03_goedkeuringsproces/02_avg-responsible-processing-records_edit_versie_select.png)

                        Een klik op de versie zal de detailpagina van deze versie tonen. Hier kunnen
                        Mandaathouders worden toegevoegd aan een versie door op "Ondertekeningen" te
                        klikken:

                        ![Ondertekeningen selecteren](/handleiding/03_goedkeuringsproces/03_snapshots_ondertekeningen.png)

                        De knop "Mandaathouders toevoegen" toont een lijst met Mandaathouders: deze
                        zijn te selecteren en kunnen worden toegevoegd met de knop "Toevoegen".

                        ![Mandaathouder toevoegen](/handleiding/03_goedkeuringsproces/04_snapshots_mandaathouder.png)

                        > **Hint**: Privacy Officers krijgen automatisch een e-mail als er een nieuwe
                        > versie is aangemaakt. Wilt u die e-mails niet ontvangen, dan zet u ze uit op
                        > uw eigen profielpagina (zie [Notificaties](#notificaties)). Wilt u één
                        > specifieke versie onder de aandacht brengen, gebruik dan de
                        > "Ondertekeningen": dat legt het verzoek vast in het portaal in plaats van
                        > alleen in iemands mailbox.

                        > **Let op**: Is eenmaal een versie aangemaakt, dan is de inhoud van deze
                        > versie niet meer aanpasbaar: slechts de status van een versie kan nog
                        > aangepast worden door een Privacy Officer. Indien er op een vastgestelde
                        > versie van een entiteit wijzigingen moeten worden aangebracht, dan is het de
                        > bedoeling dat de wijzigingen worden doorgevoerd in het formulier, de
                        > wijzigingen worden opgeslagen en er vervolgens een *nieuwe* versie wordt
                        > aangemaakt die door het goedkeuringsproces wordt geleid.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                    ],
                    availability: 'Invoerder, (Chief) Privacy Officer',
                ),
                new Topic(
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
                ),
                new Topic(
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

                        ![Mandaathouders uitnodigen](/handleiding/03_goedkeuringsproces/06_snapshots_mandaathouders_uitnodigen.png)

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
                ),
                new Topic(
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
                ),
            ],
        );
    }

    private static function beheer(): Chapter
    {
        return new Chapter(
            id: 'beheer',
            title: 'Beheer',
            summary: 'Gebruikers en bewaartermijnen binnen de organisatie.',
            topics: [
                new Topic(
                    id: 'gebruikers',
                    title: 'Gebruikers',
                    body: <<<'MARKDOWN'
                        Binnen de Organisatie zijn Gebruikers te beheren: nieuwe gebruikers kunnen
                        uitgenodigd worden, van bestaande gebruikers kunnen de rollen worden gewijzigd
                        en gebruikers kunnen verwijderd worden. Gebruikersbeheer staat in het
                        navigatiemenu onder Organisaties.

                        ### Gebruikers toevoegen

                        Rechtsboven de gebruikerstabel is de knop om gebruikers toe te voegen. Als een
                        gebruiker is toegevoegd zal deze een welkomst email toegestuurd krijgen met de
                        link naar OpenVWR.

                        ### Gebruikers aanpassen of verwijderen

                        ![Gebruikers beheer](/handleiding/04_beheer/01_users_edit.png)

                        Klik op een gebruiker in de tabel om een gebruiker te wijzigen. Op deze pagina
                        kunnen rollen worden aangepast en opgeslagen. Het verwijderen van een
                        gebruiker kan met de rode knop rechtsbovenin.
                        MARKDOWN,
                    roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                    availability: '(Chief) Privacy Officer',
                ),
                new Topic(
                    id: 'bewaartermijnen',
                    title: 'Bewaartermijnen',
                    body: <<<'MARKDOWN'
                        Bij de gegevens van een categorie betrokkenen, en bij de persoonsgegevens in
                        een DPIA, vult u per gegeven een bewaartermijn in. De termijnen waaruit u kunt
                        kiezen beheert u als opzoeklijst *Bewaartermijnen*; zie
                        [Opzoeklijsten](#opzoeklijsten) voor het toevoegen, in- en uitschakelen van
                        waarden.

                        Een bewaartermijn legt vast hoe lang u gegevens bewaart en op grond waarvan.
                        Dat is een verantwoording over een verwerking zoals die op dat moment gold, en
                        niet louter een etiket dat u er later anders op kunt plakken. Werkt u de lijst
                        bij omdat het beleid verandert, dan verandert daarmee niet met terugwerkende
                        kracht wat u eerder heeft vastgelegd, en al helemaal niet in een register dat
                        al is vastgesteld.

                        OpenVWR slaat de gekozen bewaartermijn daarom op als tekst bij het gegeven
                        zelf, en niet als verwijzing naar de lijst. De lijst levert alleen de
                        keuzemogelijkheden.

                        > **Let op**: Het aanpassen of verwijderen van een waarde in de lijst
                        > *Bewaartermijnen* verandert niets aan verwerkingen waarin die termijn al is
                        > ingevuld. Wilt u een eerder vastgelegde termijn wijzigen, dan doet u dat bij
                        > de verwerking zelf. Bij de andere opzoeklijsten werkt een wijziging wel door
                        > in de gekoppelde gegevens; die leggen een verwijzing vast in plaats van een
                        > tekst.

                        ### Een bewaartermijn kiezen

                        Staan er termijnen in de lijst, dan kiest u er één uit de keuzelijst.

                        ![Een bewaartermijn kiezen](/handleiding/04_beheer/02_retention_period_select.png)

                        Past geen van de termijnen, kies dan *Overig (zelf invullen)*. Er verschijnt
                        een tekstveld waarin u de termijn zelf beschrijft. Beschrijf daarbij niet
                        alleen hoe lang de gegevens bewaard worden, maar ook op welke grond.

                        ![Een eigen bewaartermijn invullen](/handleiding/04_beheer/03_retention_period_other.png)

                        Is de lijst *Bewaartermijnen* nog leeg, dan wordt de keuzelijst niet getoond
                        en vult u de termijn altijd zelf in. Termijnen die al eerder als vrije tekst
                        zijn ingevuld blijven gewoon staan en zijn te wijzigen.

                        > **Hint**: Vult u vaak dezelfde termijn in, laat de (Chief) Privacy Officer
                        > deze dan toevoegen aan de lijst *Bewaartermijnen*. Bij een volgende
                        > verwerking is de termijn dan met één klik te kiezen.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                    ],
                    availability: 'Invoerder, (Chief) Privacy Officer',
                ),
            ],
        );
    }

    private static function overigeFuncties(): Chapter
    {
        return new Chapter(
            id: 'overige-functies',
            title: 'Overige functies',
            summary: 'Import, export, notificaties, opzoeklijsten en publiceren.',
            topics: [
                new Topic(
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
                ),
                new Topic(
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
                ),
                new Topic(
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
                ),
                new Topic(
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
                ),
                new Topic(
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
                ),
            ],
        );
    }

    private static function labels(): Chapter
    {
        return new Chapter(
            id: 'labels',
            title: 'Labels',
            summary: 'De registratie indelen naar afdeling, locatie of werkterrein.',
            topics: [
                new Topic(
                    id: 'waarom-labels',
                    title: 'Waarom labels',
                    body: <<<'MARKDOWN'
                        Met labels richt u de registratie in op de manier waarop uw organisatie werkt.
                        Naast de inhoudelijke vastlegging van iedere verwerking legt u met een label
                        vast wáár die verwerking thuishoort: bij welke afdeling, op welke locatie,
                        binnen welk werkterrein. Zo vindt u alles in het register snel terug op basis
                        van uw eigen organisatiekenmerken, of op een tijdelijk criterium zoals een
                        lopend project of een audit.

                        Een label is een trefwoord dat u zelf bedenkt en toekent aan onderdelen van de
                        registratie: aan verwerkingen, maar net zo goed aan algoritmes, datalekken,
                        systemen, verwerkers, contactpersonen en documenten. U bepaalt zelf welke
                        labels er zijn en wat ze betekenen, en u kunt ze op elk moment uitbreiden als
                        uw organisatie verandert.

                        Labels geven u daarmee:

                        - *een indeling op maat*: u ordent de registratie naar de indeling die binnen
                          uw organisatie gangbaar is. Vaak is dat de afdeling - het RIVM gebruikt
                          bijvoorbeeld "Infectiebestrijding" of "Milieu & Veiligheid", een ziekenhuis
                          eerder "Oncologie" of "HR", een gemeente "Burgerzaken" of "Sociaal Domein".
                          Ook de locatie ("Hoftoren", "Terminal Noord", "Locatie Bilthoven") en het
                          domein waarop het werk plaatsvindt ("Administratie", "Onderzoek",
                          "Beveiliging") zijn veelgebruikte indelingen;
                        - *snel overzicht*: één label brengt in één handeling alles bij elkaar wat bij
                          bijvoorbeeld een afdeling hoort, ook als die onderdelen inhoudelijk sterk
                          verschillen. Handig bij een periodieke review, omdat u zo de afdeling kunt
                          aanspreken die de verwerking daadwerkelijk uitvoert;
                        - *gerichte rapportages*: u filtert op een label en exporteert het resultaat,
                          en heeft zo direct een overzicht per afdeling of vestiging;
                        - *samenhang tussen de registers*: hetzelfde label kan op een verwerking staan
                          én op het systeem en het document eromheen, zodat u in één beeld ziet wat er
                          bij een afdeling hoort. Zo vindt u ook een systeem dat maar op één locatie
                          draait, of de contactpersoon van die vestiging, meteen terug;
                        - *ruimte voor tijdelijke indelingen*: een label hoeft niet blijvend te zijn.
                          Met bijvoorbeeld "Herziening 2026" zet u bij elkaar wat in een ronde moet
                          worden nagelopen; is die ronde klaar, dan verwijdert u het label weer.

                        Een onderdeel kan meerdere labels tegelijk hebben en een label kan aan
                        onbeperkt veel onderdelen worden toegekend. De indelingen sluiten elkaar dus
                        niet uit: u kunt dezelfde verwerking tegelijk op afdeling, locatie én
                        werkterrein terugvinden.

                        > **Let op**: Labels horen bij één organisatie. Labels die in de ene
                        > organisatie zijn aangemaakt, zijn niet zichtbaar of bruikbaar in een andere.

                        > **Hint**: Spreek binnen de organisatie af welke indelingen u aanhoudt en hoe
                        > u ze schrijft: "HR" en "hr" zijn twee verschillende labels.
                        MARKDOWN,
                ),
                new Topic(
                    id: 'labels-toekennen',
                    title: 'Labels toekennen',
                    body: <<<'MARKDOWN'
                        ![Labels op de detailpagina van een verwerking](/handleiding/06_labels/02_avg-responsible-processing-records_edit_labels.png)

                        Op de detailpagina van een verwerking staat het veld "Labels" in het eerste
                        blok, onder de naam van de verwerking. Klik op het veld om een lijst met
                        bestaande labels te openen, of typ om te zoeken. Klik op het kruisje achter
                        een label om het van de verwerking te verwijderen. Vergeet niet de verwerking
                        op te slaan.

                        Een (Chief) Privacy Officer ziet naast het veld ook een knop "+", waarmee een
                        nieuw label kan worden aangemaakt zonder de verwerking te verlaten. Het label
                        is daarna ook voor andere verwerkingen beschikbaar. Een Invoerder kan
                        bestaande labels wel toekennen en weghalen, maar geen nieuwe labels aanmaken:
                        die knop is voor deze rol niet zichtbaar.

                        Labels zijn beschikbaar bij alle registers en bij de onderdelen onder
                        "Beheer":

                        - de verwerkingsregisters;
                        - Algoritmes en Datalekken;
                        - Verwerkingsverantwoordelijken, Verwerkers, Ontvangers,
                          Systemen/Applicaties, Contactpersonen en Documenten.

                        Het veld werkt overal hetzelfde. Zo kunt u dezelfde indeling - bijvoorbeeld
                        een afdeling of een locatie - door de hele registratie heen doorvoeren, en
                        niet alleen bij de verwerkingen.

                        ![Hetzelfde labelveld bij Systemen/Applicaties](/handleiding/06_labels/04_systems_labels.png)

                        > **Hint**: Gebruikt u hetzelfde label bij een verwerking én bij het systeem
                        > waarin die verwerking plaatsvindt, dan ziet u op de detailpagina van dat
                        > label beide onderdelen bij elkaar staan. Dat maakt het makkelijk om te
                        > overzien wat er allemaal bij een afdeling of locatie hoort.
                        MARKDOWN,
                    roles: [
                        Role::INPUT_PROCESSOR,
                        Role::INPUT_PROCESSOR_DATABREACH,
                        Role::CHIEF_PRIVACY_OFFICER,
                        Role::PRIVACY_OFFICER,
                    ],
                    availability: '(Chief) Privacy Officer (beheren), Invoerder en Invoerder '
                        . 'Datalekken (toekennen), Raadpleger (lezen), Functionaris '
                        . 'Gegevensbescherming (lezen), Mandaathouder (lezen)',
                ),
                new Topic(
                    id: 'filteren-op-labels',
                    title: 'Filteren op labels',
                    body: <<<'MARKDOWN'
                        ![Filteren op labels in het register](/handleiding/06_labels/03_avg-responsible-processing-records_filter_labels.png)

                        In elk overzicht met labels zit rechtsboven de tabel een filterknop. Onder
                        "Labels" selecteert u een of meer labels, waarna de tabel alleen nog de regels
                        met die labels toont. Met "Resetten" haalt u het filter weer weg. Het filter
                        werkt in alle overzichten die labels kennen, dus ook bij bijvoorbeeld
                        Systemen/Applicaties of Documenten.

                        > **Hint**: De export volgt de filters van de tabel. Filtert u eerst op een
                        > label en exporteert u daarna, dan bevat het bestand alleen de regels van dat
                        > label. De labels zelf staan als kolom "Labels" in de export. Zo maakt u
                        > bijvoorbeeld een overzicht per afdeling. Voor meer informatie over
                        > exporteren: zie [Export](#export).
                        MARKDOWN,
                ),
                new Topic(
                    id: 'labels-beheren',
                    title: 'Labels beheren',
                    body: <<<'MARKDOWN'
                        ![Labeloverzicht](/handleiding/06_labels/01_tags.png)

                        In het navigatiemenu staat onder "Beheer" het onderdeel "Labels". Hier ziet u
                        alle labels van de organisatie. Met de knop "Label aanmaken" rechtsboven
                        voegt u een label toe; met het potloodje achter een regel wijzigt u de naam of
                        de kleur van een bestaand label.

                        Klik op een label om te zien waar het overal aan is toegekend. Per soort
                        onderdeel is er een tabel - verwerkingen, algoritmes, datalekken, systemen,
                        verwerkers, contactpersonen, documenten enzovoort - zodat u direct kunt
                        doorklikken.

                        ### De kleur van een label

                        Elk label heeft een kleur, zodat u in een volle tabel in één oogopslag ziet
                        welk label waar staat. Een nieuw label krijgt automatisch een kleur die binnen
                        uw organisatie nog niet of het minst in gebruik is, zodat de kleuren vanzelf
                        gespreid blijven. U hoeft er dus niets voor te doen.

                        Wilt u zelf een kleur kiezen - bijvoorbeeld groen voor "Akkoord" of een vaste
                        kleur per afdeling - dan doet u dat in het veld "Kleur" op het scherm van het
                        label. Er zijn tien kleuren. Rood zit er bewust niet bij: die kleur is in de
                        applicatie voorbehouden aan waarschuwingen en aan statussen, en een label mag
                        daar niet mee worden verward.

                        > **Let op**: De kleur is een hulpmiddel, geen betekenisdrager. De naam van
                        > het label staat er altijd bij, ook voor wie kleuren niet of anders
                        > waarneemt. Laat de betekenis van een label dus nooit alleen van de kleur
                        > afhangen.

                        > **Let op**: Het verwijderen van een label verwijdert het overal: de
                        > onderdelen zelf blijven bestaan, maar het label is er overal af. Wilt u een
                        > label maar bij één onderdeel weghalen, doe dat dan via het veld "Labels" op
                        > de detailpagina van dat onderdeel.

                        Het wijzigen van de naam van een label werkt wél overal door: de onderdelen
                        houden het label, alleen de naam verandert. Dat is de aangewezen manier om een
                        schrijfwijze recht te trekken zonder de koppelingen kwijt te raken.
                        MARKDOWN,
                    roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
                    availability: '(Chief) Privacy Officer',
                ),
            ],
        );
    }

    private static function rollenEnRechten(): Chapter
    {
        return new Chapter(
            id: 'rollen-en-rechten',
            title: 'Rollen en rechten',
            summary: 'Welke rol welke onderdelen van het portaal mag gebruiken.',
            topics: [
                new Topic(
                    id: 'rollen',
                    title: 'De rollen in het portaal',
                    body: <<<'MARKDOWN'
                        Iedere gebruiker heeft een of meer rollen binnen een organisatie. De rol
                        bepaalt welke onderdelen van het portaal zichtbaar zijn en welke acties
                        uitgevoerd mogen worden. Een gebruiker kan per organisatie meerdere rollen
                        tegelijk hebben.

                        Rollen worden toegekend door een Chief Privacy Officer of Privacy Officer.
                        Voor het toekennen en wijzigen van rollen: zie [Gebruikers](#gebruikers).

                        Niet elke organisatie gebruikt alle rollen. Welke rollen bij u in gebruik
                        zijn, hangt af van de manier waarop uw organisatie het beheer van het register
                        heeft ingericht. Rollen die uw organisatie niet toekent, komt u in de praktijk
                        dan ook niet tegen.

                        ### Chief Privacy Officer

                        De Chief Privacy Officer is het aanspreekpunt voor toegang tot het portaal
                        binnen de organisatie. Deze rol heeft de meeste rechten en kan:

                        - verwerkingen, algoritmes en datalekken aanmaken, wijzigen en verwijderen;
                        - documenten en verantwoordelijken beheren;
                        - versies aanmaken en het goedkeuringsproces begeleiden (goedkeuren,
                          vaststellen en vervallen);
                        - registers importeren en exporteren;
                        - opzoeklijsten en labels beheren;
                        - gebruikers uitnodigen en rollen toekennen.

                        ### Privacy Officer

                        De Privacy Officer voert dezelfde taken uit als de Chief Privacy Officer, met
                        één uitzondering bij het gebruikersbeheer: een Privacy Officer kan geen Chief
                        Privacy Officer of Mandaathouder rollen toekennen. Alleen een Chief Privacy
                        Officer kan deze rollen toewijzen.

                        ### Invoerder

                        De Invoerder voert gegevens in het register in en bereidt verwerkingen voor op
                        het goedkeuringsproces. Een Invoerder kan:

                        - verwerkingen en algoritmes aanmaken, wijzigen en verwijderen;
                        - documenten en verantwoordelijken beheren;
                        - versies aanmaken en Mandaathouders koppelen aan een versie;
                        - verwerkingen, algoritmes en versies bekijken.

                        Een Invoerder kan geen versies goedkeuren of vaststellen, geen registers
                        importeren of exporteren, geen opzoeklijsten beheren en geen gebruikers
                        beheren.

                        ### Invoerder Datalekken

                        De Invoerder Datalekken is gericht op het datalekregister. Deze rol kan
                        datalekken aanmaken, wijzigen en verwijderen, inclusief gekoppelde documenten
                        en verantwoordelijken.

                        Daarnaast kan een Invoerder Datalekken verwerkingen en algoritmes bekijken,
                        maar niet aanmaken of wijzigen. Deze rol kan geen versies aanmaken of het
                        goedkeuringsproces uitvoeren.

                        ### Mandaathouder

                        De Mandaathouder leest registers en geeft akkoord op versies die zijn
                        goedgekeurd door een Privacy Officer. Een Mandaathouder kan:

                        - verwerkingen, algoritmes, documenten en versies bekijken;
                        - akkoord of niet akkoord geven op versies waarvoor hij of zij is uitgenodigd;
                        - op de profielpagina voorkeuren instellen voor email notificaties over
                          versies.

                        Een Mandaathouder kan geen gegevens invoeren of wijzigen en geen versies
                        goedkeuren of vaststellen.

                        ### Raadpleger

                        De Raadpleger heeft alleen leesrechten. Deze rol kan registers, documenten,
                        versies en het goedkeuringsproces bekijken, maar geen gegevens invoeren,
                        wijzigen of verwijderen.

                        ### Functionaris Gegevensbescherming

                        De Functionaris Gegevensbescherming (FG) heeft leesrechten vergelijkbaar met
                        de Raadpleger, met twee aanvullingen.

                        Ten eerste kan een FG opmerkingen plaatsen bij verwerkingen. Deze opmerkingen
                        zijn alleen zichtbaar voor Functionarissen Gegevensbescherming.

                        Ten tweede kan een FG registers exporteren naar een `.csv` of `.xlsx`
                        bestand. Zie [Export](#export).

                        ### Functioneel beheerder

                        De Functioneel beheerder beheert de applicatie zelf: organisaties, gebruikers
                        en globale instellingen. Deze rol kent rollen toe en heeft inzage in het
                        beheerlogboek. Het is een rol op applicatieniveau en niet binnen één
                        organisatie: een Functioneel beheerder werkt niet in de registers zelf.
                        MARKDOWN,
                ),
                new Topic(
                    id: 'rechten-per-onderdeel',
                    title: 'Overzicht per onderdeel',
                    body: <<<'MARKDOWN'
                        Onderstaand overzicht geeft per onderdeel aan welke rollen toegang hebben.
                        Waar "(lezen)" staat, geldt dat de rol alleen kan bekijken.

                        ### Registers (verwerkingen en algoritmes)

                        Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris
                        Gegevensbescherming (lezen), Mandaathouder (lezen).

                        ### Datalekken

                        Invoerder Datalekken, (Chief) Privacy Officer, Raadpleger (lezen),
                        Functionaris Gegevensbescherming (lezen).

                        ### Goedkeuringsproces

                        - *Versie aanmaken*: Invoerder, (Chief) Privacy Officer;
                        - *Goedkeuren en vaststellen*: (Chief) Privacy Officer;
                        - *Akkoord geven*: Mandaathouder.

                        ### Import, export en opzoeklijsten

                        - *Import en opzoeklijsten*: (Chief) Privacy Officer;
                        - *Export*: (Chief) Privacy Officer, Functionaris Gegevensbescherming.

                        ### Gebruikersbeheer

                        (Chief) Privacy Officer. Een Privacy Officer kan ook gebruikers beheren, maar
                        niet de rollen Chief Privacy Officer en Mandaathouder toekennen.
                        MARKDOWN,
                ),
            ],
        );
    }
}
