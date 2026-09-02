<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Manual\Chapter;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class RollenEnRechten
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'rollen-en-rechten',
            title: 'Rollen en rechten',
            summary: 'Welke rol welke onderdelen van het portaal mag gebruiken.',
            topics: [
                self::rollen(),
                self::rechtenPerOnderdeel(),
            ],
        );
    }

    private static function rollen(): Topic
    {
        return new Topic(
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
                MARKDOWN . self::rolbeschrijvingen(),
        );
    }

    private static function rechtenPerOnderdeel(): Topic
    {
        return new Topic(
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

                - *Start vaststellen (versie indienen)*: Invoerder, (Chief) Privacy Officer;
                - *Goedkeuren en vaststellen*: (Chief) Privacy Officer;
                - *Akkoord geven*: Mandaathouder.

                ### Import, export en opzoeklijsten

                - *Import en opzoeklijsten*: (Chief) Privacy Officer;
                - *Export*: (Chief) Privacy Officer, Functionaris Gegevensbescherming.

                ### Gebruikersbeheer

                (Chief) Privacy Officer. Een Privacy Officer kan ook gebruikers beheren, maar
                niet de rollen Chief Privacy Officer en Mandaathouder toekennen.
                MARKDOWN,
        );
    }

    /**
     * What each individual role may do. Split from the introduction above only
     * to keep either piece a readable length; it is one topic to the reader.
     */
    private static function rolbeschrijvingen(): string
    {
        return <<<'MARKDOWN'

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
            MARKDOWN;
    }
}
