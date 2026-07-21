# Rollen en Rechten\label{RollenEnRechten}

Iedere gebruiker heeft een of meer rollen binnen een organisatie. De rol bepaalt welke onderdelen van het portaal zichtbaar zijn en welke acties uitgevoerd mogen worden. Een gebruiker kan per organisatie meerdere rollen tegelijk hebben.

Rollen worden toegekend door een Chief Privacy Officer of Privacy Officer. Voor het toekennen en wijzigen van rollen: zie Hoofdstuk \ref{Beheer}, "Beheer".

Het portaal kent de volgende rollen:

- Chief Privacy Officer
- Privacy Officer
- Invoerder
- Invoerder Datalekken
- Mandaathouder
- Raadpleger
- Functionaris Gegevensbescherming

## Chief Privacy Officer

De Chief Privacy Officer is het aanspreekpunt voor toegang tot het portaal binnen de organisatie. Deze rol heeft de meeste rechten.

Een Chief Privacy Officer kan:

- verwerkingen, algoritmes en datalekken aanmaken, wijzigen en verwijderen;
- documenten en verantwoordelijken beheren;
- versies aanmaken en het goedkeuringsproces begeleiden (goedkeuren, vaststellen en vervallen);
- registers importeren en exporteren;
- opzoeklijsten en tags beheren;
- gebruikers uitnodigen en rollen toekennen


## Privacy Officer

De Privacy Officer voert dezelfde taken uit als de Chief Privacy Officer, met één uitzondering bij het gebruikersbeheer: een Privacy Officer kan geen Chief Privacy Officer of Mandaathouder rollen toekennen. Alleen een Chief Privacy Officer kan deze rollen toewijzen.

Verder kan een Privacy Officer:

- verwerkingen, algoritmes en datalekken aanmaken, wijzigen en verwijderen;
- documenten en verantwoordelijken beheren;
- versies aanmaken en het goedkeuringsproces begeleiden (goedkeuren, vaststellen en vervallen);
- registers importeren en exporteren;
- opzoeklijsten en tags beheren;
- gebruikers uitnodigen en rollen toekennen, met uitzondering van Chief Privacy Officer en Mandaathouder.

## Invoerder

De Invoerder voert gegevens in het register in en bereidt verwerkingen voor op het goedkeuringsproces.

Een Invoerder kan:

- verwerkingen en algoritmes aanmaken, wijzigen en verwijderen;
- documenten en verantwoordelijken beheren;
- versies aanmaken en Mandaathouders koppelen aan een versie;
- verwerkingen, algoritmes en versies bekijken.

Een Invoerder kan geen versies goedkeuren of vaststellen, geen registers importeren of exporteren, geen opzoeklijsten beheren en geen gebruikers beheren.

> **Hint**: Voor het goedkeuringsproces: zie Hoofdstuk \ref{Goedkeuringsproces}, "Goedkeuringsproces".

## Invoerder Datalekken

De Invoerder Datalekken is gericht op het datalekregister. Deze rol kan datalekken aanmaken, wijzigen en verwijderen, inclusief gekoppelde documenten en verantwoordelijken.

Daarnaast kan een Invoerder Datalekken verwerkingen en algoritmes bekijken, maar niet aanmaken of wijzigen. Deze rol kan geen versies aanmaken of het goedkeuringsproces uitvoeren.

## Mandaathouder

De Mandaathouder leest registers en geeft akkoord op versies die zijn goedgekeurd door een Privacy Officer. Akkoord geven kan op de detailpagina van een versie.

Een Mandaathouder kan:

- verwerkingen, algoritmes, documenten en versies bekijken;
- akkoord of niet akkoord geven op versies waarvoor hij of zij is uitgenodigd;
- op de profielpagina voorkeuren instellen voor email notificaties over versies.

Een Mandaathouder kan geen gegevens invoeren of wijzigen en geen versies goedkeuren of vaststellen.

> **Hint**: Voor het akkoord geven op versies: zie Hoofdstuk \ref{Goedkeuringsproces}, "Goedkeuringsproces".

## Raadpleger

De Raadpleger heeft alleen leesrechten. Deze rol kan registers, documenten, versies en het goedkeuringsproces bekijken, maar geen gegevens invoeren, wijzigen of verwijderen.

## Functionaris Gegevensbescherming

De Functionaris Gegevensbescherming (FG) heeft leesrechten vergelijkbaar met de Raadpleger, met twee aanvullingen.

Ten eerste kan een FG opmerkingen plaatsen bij verwerkingen. Deze opmerkingen zijn alleen zichtbaar voor Functionarissen Gegevensbescherming.

Ten tweede kan een FG registers exporteren naar een `.csv` of `.xlsx` bestand. Voor meer informatie over exporteren: zie Hoofdstuk \ref{OverigeFuncties}, "Overige Functies".

## Overzicht per onderdeel

Onderstaand overzicht geeft per onderdeel aan welke rollen toegang hebben. Waar "(lezen)" staat, geldt dat de rol alleen kan bekijken.

### Registers (verwerkingen en algoritmes)

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)

### Datalekken

**Beschikbaar voor**: Invoerder Datalekken, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen)

### Goedkeuringsproces

**Versie aanmaken**: Invoerder, (Chief) Privacy Officer

**Goedkeuren en vaststellen**: (Chief) Privacy Officer

**Akkoord geven**: Mandaathouder

### Import, export en opzoeklijsten

**Import en opzoeklijsten**: (Chief) Privacy Officer

**Export**: (Chief) Privacy Officer, Functionaris Gegevensbescherming

### Gebruikersbeheer

**Beschikbaar voor**: (Chief) Privacy Officer

Een Privacy Officer kan ook gebruikers beheren, maar niet de rollen Chief Privacy Officer en Mandaathouder toekennen.
