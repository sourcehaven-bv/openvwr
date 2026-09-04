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
final class Beheer
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'beheer',
            title: 'Beheer',
            summary: 'Gebruikers, tweefactorauthenticatie, bewaartermijnen en de openbare '
                . 'website.',
            topics: [
                self::gebruikers(),
                self::tweefactorResetten(),
                self::bewaartermijnen(),
                self::websitebeheer(),
            ],
        );
    }

    private static function gebruikers(): Topic
    {
        return new Topic(
            id: 'gebruikers',
            title: 'Gebruikers',
            body: <<<'MARKDOWN'
                Via het Organisatie - Gebruikers kunnen nieuwe gebruikers uitgenodigd, de
                rollen van bestaande gebruikers gewijzigd en gebruikers verwijderd worden.

                ### Gebruikers toevoegen

                ![De knop "Gebruiker aanmaken" boven de gebruikerstabel](/handleiding/04_beheer/04_users_toevoegen.png)

                Rechtsboven de gebruikerstabel is de knop om gebruikers toe te voegen. Als een
                gebruiker is toegevoegd zal deze een welkomst email toegestuurd krijgen met de
                link naar OpenVWR.

                ### Gebruikers aanpassen of verwijderen

                ![Gebruikers beheer](/handleiding/04_beheer/01_users_edit.png)

                Klik op een gebruiker in de tabel om een gebruiker te wijzigen. Op deze pagina
                kunnen rollen worden aangepast en opgeslagen. Het verwijderen van een
                gebruiker kan met de rode knop rechtsbovenin.

                Een belangrijke mogelijkheid is het resetten van de tweefactorauthenticatie
                van een gebruiker. Zie
                [Tweefactorauthenticatie resetten](#tweefactor-resetten) voor meer informatie.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function tweefactorResetten(): Topic
    {
        return new Topic(
            id: 'tweefactor-resetten',
            title: 'Tweefactorauthenticatie resetten',
            body: <<<'MARKDOWN'
                Tweefactorauthenticatie is verplicht. De code voor de tweede factor komt uit
                een authenticator applicatie op het toestel van de gebruiker zelf. Raakt
                iemand dat toestel kwijt of neemt hij een nieuw toestel in gebruik, dan kan
                het zijn dat hij niet meer kan inloggen. Via het gebruikersbeheer
                (Organisatie - Gebruikers - Kies gebruiker) kunt u de tweefactorauthenticatie
                dan voor hem resetten.

                U doet dat op de bewerkpagina van de gebruiker, met de knop "2FA resetten"
                rechtsbovenin. Er volgt eerst een bevestiging.

                ![De knop "2FA resetten" op de bewerkpagina van een gebruiker](/handleiding/04_beheer/01_users_edit.png)

                Na het resetten kan de gebruiker weer inloggen met zijn e-mailadres. Bij de
                eerstvolgende keer inloggen zal OpenVWR vragen de authenticator opnieuw in
                te stellen.

                Zie
                [De authenticator instellen](#authenticator-instellen). U hoeft verder niets
                te doen. Bij het resetten verstuurt OpenVWR geen bericht. Laat de gebruiker
                dus zelf weten dat hij weer terecht kan.

                > **Let op**: Reset de tweefactorauthenticatie alleen als u zeker weet dat u
                > met de juiste persoon te maken heeft. De reset haalt een beveiligingslaag
                > weg: wie bij de inloglink kan, komt binnen zonder tweede factor.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }

    private static function bewaartermijnen(): Topic
    {
        return new Topic(
            id: 'bewaartermijnen',
            title: 'Standaard bewaartermijnen beheren',
            body: <<<'MARKDOWN'
                Bij de gegevens van een categorie betrokkenen, en bij de persoonsgegevens in
                een DPIA, vult u per gegeven een bewaartermijn in. De lijst van termijnen
                waaruit gekozen kan worden wordt beheerd als opzoeklijst *Bewaartermijnen*;
                zie
                [Opzoeklijsten](#opzoeklijsten) voor het toevoegen, in- en uitschakelen van
                waarden.

                ![De opzoeklijst Bewaartermijnen](/handleiding/05_overige_functies/04_lookup_list_edit.png)

                De bewaartermijnenlijst werkt anders dan de andere opzoeklijsten. Bij de
                andere opzoeklijsten wordt een verwijzing naar de gekozen waarde opgeslagen.
                Bij de bewaartermijnenlijst wordt de gekozen waarde opgeslagen bij het
                gegeven zelf.

                De gedachte hierachter is dat een bewaartermijn vastlegt hoe lang u gegevens
                bewaart en op grond waarvan. Dat is een verantwoording over een verwerking
                zoals die op dat moment gold. Het automatisch aanpassen op alle bestaande
                verwerkingen zou daarmee ongewenste effecten hebben.

                > **Let op**: Het aanpassen of verwijderen van een waarde in de lijst
                > *Bewaartermijnen* verandert niets aan verwerkingen waarin die termijn al is
                > ingevuld. Wilt u een eerder vastgelegde termijn wijzigen, dan doet u dat bij
                > de verwerking zelf.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer',
        );
    }

    private static function websitebeheer(): Topic
    {
        return new Topic(
            id: 'websitebeheer',
            title: 'De openbare website inrichten',
            body: <<<'MARKDOWN'
                De openbare website toont de verwerkingen die een Privacy Officer openbaar
                heeft gemaakt. Welke verwerkingen dat zijn, bepaalt die Privacy Officer; zie
                [Publiceren](#publiceren). De inrichting van de website eromheen is werk van
                een Functioneel beheerder, en dat is wat dit onderwerp beschrijft.

                ### De paginaboom

                Onder Functioneel beheer - Website organogram bouwt u de paginaboom van de
                openbare website. Elk item is een pagina; door items onder elkaar te hangen
                ontstaat de structuur die de bezoeker in de navigatie ziet.

                ![Het Website organogram, met de knop om een item toe te voegen](/handleiding/04_beheer/05_public-website-tree.png)

                Per item legt u vast:

                - *Titel*: de naam van de pagina, zoals die in de navigatie komt.
                - *URL-segment*: bepaalt het webadres van deze pagina. Wijzigt u dit later,
                  dan verandert het adres mee en werken oude links niet meer.
                - *Tekst publieke website*: de inhoud van de pagina zelf.
                - *Organisatie*: de organisatie waaronder de pagina valt. Hiermee koppelt u
                  een deel van de boom aan de verwerkingen van die organisatie.
                - *Publieke URL*: alleen invullen als het item juist niet naar een eigen
                  pagina moet wijzen, maar naar een externe website.

                ### Wanneer een pagina live gaat

                Net als bij een verwerking bepaalt een publicatiedatum wanneer een pagina
                zichtbaar wordt. Staat er geen datum, dan blijft de pagina onzichtbaar voor
                bezoekers; staat er een datum in de toekomst, dan verschijnt hij vanaf dan.
                In het overzicht ziet u dat terug als *(geen publicatiedatum)* of *(live
                vanaf ...)*.

                > **Let op**: De website wordt periodiek opnieuw opgebouwd. Een wijziging in
                > de paginaboom is dus niet meteen zichtbaar voor bezoekers, net zoals een
                > nieuw gepubliceerde verwerking pas bij de volgende bouw meekomt.
                MARKDOWN,
            roles: [Role::FUNCTIONAL_MANAGER],
            gate: FeatureGate::PUBLISHING,
            availability: 'Functioneel beheerder',
        );
    }
}
