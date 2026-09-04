<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class Dpia
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'dpia',
            title: 'DPIA',
            summary: 'De pre-scan, de DPIA zelf, en het vaststellen ervan.',
            topics: [
                self::preScan(),
                self::dpiaInvullen(),
                self::risicoEnMaatregelen(),
                self::dpiaVaststellen(),
            ],
        );
    }

    private static function preScan(): Topic
    {
        return new Topic(
            id: 'dpia-prescan',
            title: 'Pre-scan DPIA: is een DPIA nodig',
            body: <<<'MARKDOWN'
                Naast de verwerkingsregisters heeft OpenVWR een apart onderdeel voor de
                gegevensbeschermingseffectbeoordeling (DPIA). U vindt het in het
                navigatiemenu onder *DPIA*, met twee registers: de Pre-scan DPIA en de DPIA
                zelf.

                Een pre-scan is een korte toets waarmee u bepaalt óf een verwerking een
                volledige DPIA nodig heeft. Dezelfde toets laat ook zien of er een DTIA
                (internationale doorgifte), een KIA (kinderrechten) of een IAMA (algoritmes
                en hoogrisico-AI) aan de orde is.

                U doorloopt negen stappen: *Algemeen*, *Aanleiding*, *AP-criteria*,
                *EDPB-criteria*, *Doorgifte*, *Kinderen en algoritmes*, *Uitkomst*,
                *Verwerkingen en systemen* en *Documenten en bijlagen*. De stap *Uitkomst*
                vat samen wat de antwoorden betekenen.

                > **Hint**: Een pre-scan blijft bewaard, ook als de uitkomst is dat er géén
                > DPIA nodig is. Juist dan is hij waardevol: hij legt vast dat u de afweging
                > gemaakt hebt, en op welke gronden.

                ### Van pre-scan naar DPIA

                Geeft de uitkomst aanleiding tot een DPIA, dan verschijnt op de bewerkpagina
                van de pre-scan de knop "DPIA starten". Die maakt een nieuwe DPIA aan waarin
                de naam, de omschrijving en de gekoppelde verwerkingen al zijn overgenomen.

                > **Let op**: Een pre-scan doorloopt zelf geen goedkeuringsproces. Er is geen
                > versie en geen status: de pre-scan is een hulpmiddel om te bepalen wat er
                > moet gebeuren. Voor de DPIA die eruit voortkomt geldt dat wél; zie
                > [Een DPIA laten vaststellen](#dpia-vaststellen).

                Deze handleiding beschrijft hoe u de module bedient. Voor de inhoudelijke
                methodiek - welke vragen u hoe beantwoordt en hoe u een risico weegt - volgt
                OpenVWR het Model DPIA Rijksdienst. Raadpleeg dat model; het wordt landelijk
                onderhouden en is leidend boven wat hier staat.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer; Raadpleger, Functionaris '
                . 'Gegevensbescherming en Mandaathouder lezen mee',
        );
    }

    private static function dpiaInvullen(): Topic
    {
        return new Topic(
            id: 'dpia-invullen',
            title: 'De DPIA invullen',
            body: <<<'MARKDOWN'
                Een DPIA is opgebouwd volgens de paragrafen van het Model DPIA Rijksdienst.
                Op het invoerformulier doorloopt u ze als stappen, van *Algemeen* en
                *1. Voorstel* tot en met *17. Maatregelen*, gevolgd door *Consultatie en
                advies*, *Vaststelling en herziening*, *Verwerkingen en systemen*,
                *Documenten en bijlagen* en *Opmerkingen*.

                U hoeft dat niet in één keer te doen. Net als bij een verwerking slaat u
                tussentijds op; het portaal bewaart uw werk als conceptversie.

                ### Persoonsgegevens

                Paragraaf *2. Persoonsgegevens* is een van de belangrijkste. Per gegeven legt
                u vast om welk type het gaat - gewoon, gevoelig, bijzonder, strafrechtelijk
                of een identificatienummer - en wat de categorie betrokkenen, de bron en de
                bewaartermijn zijn.

                Gaat het om bijzondere persoonsgegevens, strafrechtelijke gegevens of een
                identificatienummer, dan vraagt het formulier verplicht om een
                uitzonderingsgrond.

                > **Hint**: De bewaartermijn werkt hier hetzelfde als bij een verwerking: u
                > kiest uit de opzoeklijst *Bewaartermijnen*, en de gekozen waarde wordt bij
                > het gegeven zelf bewaard. Zie
                > [Standaard bewaartermijnen beheren](#bewaartermijnen).

                ### Koppelen aan verwerkingen

                Onder *Verwerkingen en systemen* koppelt u de DPIA aan de verwerkingen en
                systemen waar hij over gaat. Start u de DPIA vanuit een pre-scan, dan zijn
                die koppelingen al ingevuld.

                Een DPIA is te dupliceren en van labels te voorzien, net als een verwerking;
                zie [Labels toekennen](#labels-toekennen).
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer; Raadpleger, Functionaris '
                . 'Gegevensbescherming en Mandaathouder lezen mee',
        );
    }

    private static function risicoEnMaatregelen(): Topic
    {
        return new Topic(
            id: 'dpia-risicos',
            title: "Risico's en maatregelen",
            body: <<<'MARKDOWN'
                In paragraaf *16. Risico's voor betrokkenen* legt u de risico's vast, elk met
                een inschatting van de kans dat het zich voordoet en van de impact als dat
                gebeurt. In *17. Maatregelen* beschrijft u wat u doet om een risico te
                beperken, en koppelt u die maatregel aan het risico waar hij bij hoort.

                Het portaal rekent kans en impact om naar een risiconiveau. Dat is een
                hulpmiddel, geen oordeel: de weging blijft aan u en aan de Functionaris
                Gegevensbescherming.

                ### Als er een hoog restrisico overblijft

                Blijft er ná de maatregelen een hoog risico staan, dan wijst OpenVWR erop dat
                een voorafgaande raadpleging van de Autoriteit Persoonsgegevens verplicht is
                (artikel 36 AVG). Dat legt u vast bij *Consultatie en advies*, samen met het
                advies van de Functionaris Gegevensbescherming.

                Bij *Vaststelling en herziening* geeft u aan wanneer de DPIA opnieuw tegen
                het licht gehouden moet worden. Houd daarbij een termijn van maximaal drie
                jaar aan.

                > **Let op**: Een DPIA beschrijft de situatie zoals die op dat moment gold.
                > Verandert de verwerking wezenlijk, wacht dan niet op de herzieningsdatum
                > maar beoordeel de DPIA opnieuw.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer; Raadpleger, Functionaris '
                . 'Gegevensbescherming en Mandaathouder lezen mee',
        );
    }

    private static function dpiaVaststellen(): Topic
    {
        return new Topic(
            id: 'dpia-vaststellen',
            title: 'Een DPIA laten vaststellen',
            body: <<<'MARKDOWN'
                Een DPIA doorloopt hetzelfde goedkeuringsproces als een verwerking. Is hij
                klaar, dan dient u hem in met de knop "Start vaststellen" rechtsbovenin de
                bewerkpagina. Daarna volgt review door een Privacy Officer, eventueel een
                akkoord van Mandaathouders, en het vaststellen.

                Het proces, de statussen en de rollen zijn identiek aan die van een
                verwerking; zie [Het proces en de statussen](#versiestatussen) en
                [Versie indienen en Mandaathouders koppelen](#versie-indienen).

                > **Let op**: Anders dan een verwerking is een DPIA **niet** te publiceren op
                > de openbare website. Een vastgestelde DPIA blijft binnen het portaal.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer; Raadpleger, Functionaris '
                . 'Gegevensbescherming en Mandaathouder lezen mee',
        );
    }
}
