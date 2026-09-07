# Testscript OpenVWR — Import met mapping (Excel/CSV)

Dit testscript ondersteunt handmatige QA van de importfunctie onder "Import" (navigatiegroep Functioneel beheer). Het volgt de sectie "Import" uit het hoofdstuk "Overige functies" van de handleiding. Elke regel in een tabel heeft een uniek, doorlopend nummer.

Vink een regel af in de kolom "Test Geslaagd" zodra de stap is uitgevoerd en het verwachte resultaat klopt. Noteer bij een afwijking of bijzonderheid altijd een toelichting in "Opmerkingen", ook als de stap slaagt maar iets opviel.

**Tester:** ________________________ &nbsp;&nbsp; **Datum:** ________________________ &nbsp;&nbsp; **Omgeving/versie:** ________________________

## Voorbereiding

- Test op een omgeving met een **lege of wegwerp-organisatie**: de import maakt echte records aan.
- De testbestanden staan in `docs/qa/fixtures/import-mapping/`. Ze bevatten uitsluitend verzonnen gegevens.
- Je hebt accounts nodig met de rollen **(Chief) Privacy Officer** (mag importeren), **Invoerder** (mag niet importeren) en een gebruiker in een **tweede organisatie**.

| Bestand                               | Inhoud                                                                                                                                       |
| ------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| `01-datalekken-schoon.xlsx`           | 3 datalekken met Nederlandse kolomkoppen die één-op-één op OpenVWR-velden passen. Datums als Excel-datumcellen, ja/nee-kolommen, een lijstkolom. |
| `02-zenya-vim-export.xlsx`            | 3 VIM-meldingen zoals een extern systeem ze exporteert: eigen kolomnamen, een meldnummer, en kolommen met meldergegevens die niet mee mogen. |
| `03-datalekken-met-fouten.xlsx`       | 5 rijen waarvan 2 goed, 1 met "misschien" in een ja/nee-kolom, 1 zonder naam, 1 met "n.v.t." in een datumkolom.                             |
| `04-datalekken.csv`                   | Dezelfde opzet als 01, maar als CSV met 2 rijen.                                                                                              |
| `05-verwerkingen-met-verwerkers.csv`  | 3 AVG-verwerkingen met verwerkers (inclusief e-mail en adres), een dienst en een systeem. Bevat een afwijkende schrijfwijze en een cel met twee verwerkers. |

---

## 1. Toegang

*Bron: handleiding "Overige functies" > "Import", kop "Beschikbaar voor".*

| Nr  | Testactie                                                                                                                                                                  | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 1   | Log in als (Chief) Privacy Officer en controleer dat onder "Functioneel beheer" het menu-item "Import" staat en de pagina opent met een uitleg en een uploadveld.              | [ ]           |             |
| 2   | Log in als Invoerder en controleer dat "Import" niet in het menu staat en dat de pagina via de URL `/import` ook niet toegankelijk is (403 of doorverwijzing).               | [ ]           |             |
| 3   | Controleer als (Chief) Privacy Officer dat het oude importscherm voor zip-bestanden niet meer apart in het menu staat, maar dat de oude URL `/import-archive` nog wel werkt.  | [ ]           |             |

---

## 2. Bestand kiezen

*Bron: handleiding, kop "Bestand kiezen".* De eerste rij van een Excel- of CSV-bestand moet de kolomnamen bevatten. Na het uploaden wordt het bestand direct geanalyseerd; er is geen aparte knop.

| Nr  | Testactie                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 4   | Kies bij "Importeren als" het register **Datalekken** en upload `01-datalekken-schoon.xlsx`. Controleer dat het scherm zonder extra klik doorgaat naar "Mapping controleren" en "3 rijen gevonden" meldt. | [ ]           |             |
| 5   | Klik "Opnieuw beginnen", upload `04-datalekken.csv` met register Datalekken en controleer dat CSV op dezelfde manier wordt gelezen ("2 rijen gevonden").                                        | [ ]           |             |
| 6   | Upload een bestand van een ander type (bijvoorbeeld een `.pdf` of `.docx`) en controleer dat het geweigerd wordt met een foutmelding, zonder dat het scherm vastloopt.                          | [ ]           |             |
| 7   | Upload een Excel-bestand met alléén een kopregel en geen gegevensrijen. Controleer dat de melding "Het bestand bevat alleen een kopregel en geen gegevens" verschijnt en het scherm op de uploadstap blijft.                | [ ]           |             |
| 8   | Upload een Excel-bestand zonder kopregel (gegevens in rij 1). Controleer dat de eerste rij als kolomnamen wordt getoond, zodat de tester ziet dat het bestand niet klopt; er mag niets crashen.  | [ ]           |             |
| 9   | Upload een OpenVWR-export (`.zip`, bijvoorbeeld gemaakt via "Exporteren" in een ander register of een andere OpenVWR-omgeving) zonder een register te kiezen. Controleer dat het scherm "OpenVWR-export gevonden" toont met per register het aantal records. | [ ]           |             |
| 10  | Klik in dat scherm op "Importeren" en controleer dat de records in de betreffende registers verschijnen en dat er een notificatie komt zoals bij de oude zip-import.                            | [ ]           |             |
| 11  | Upload een `.zip` die geen OpenVWR-export is (bijvoorbeeld een gezipte foto). Controleer dat "In dit bestand zijn geen herkenbare registers gevonden" verschijnt.                              | [ ]           |             |

---

## 3. Mapping controleren

*Bron: handleiding, kop "Mapping controleren".* Per kolom toont het scherm de kolomnaam, voorbeeldwaarden, het gekozen veld en de status van de koppeling.

| Nr  | Testactie                                                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 12  | Upload `01-datalekken-schoon.xlsx` (register Datalekken). Controleer dat alle kolommen de status "Automatisch ingevuld" hebben en dat de melding "Alle kolommen zijn automatisch herkend" verschijnt.                              | [ ]           |             |
| 13  | Controleer per kolom dat er voorbeeldwaarden uit het bestand onder de kolomnaam staan en dat datums leesbaar zijn (geen getallen zoals 46085).                                                                                 | [ ]           |             |
| 14  | Controleer dat onder het gekozen veld staat hoe de waarde gelezen wordt ("Wordt gelezen als: Datum", "Ja/nee", "Tekst", "Lijst (regel per waarde)") en dat dit past bij het veld.                                             | [ ]           |             |
| 15  | Wijzig bij één kolom het veld naar iets anders en controleer dat de status verandert in "Zelf gekozen" en het type meebeweegt met het nieuwe veld.                                                                             | [ ]           |             |
| 16  | Upload `02-zenya-vim-export.xlsx` (register Datalekken). Controleer dat kolommen als "Melder" en "Afdeling" op "— niet importeren —" staan met status "Nog geen keuze gemaakt", en dat onbekende namen niet aan een verkeerd veld gekoppeld zijn. | [ ]           |             |
| 17  | Controleer in datzelfde bestand dat "Melding AP", "Melding FG" en "Betrokkene geinformeerd" op grond van hun inhoud (ja/nee) als ja/nee-veld herkend zijn, ook al lijken de namen niet op de veldnamen.                          | [ ]           |             |
| 18  | Koppel "Meldnummer" aan "Bronkenmerk (nummer uit het bronsysteem)" onder de groep Herkomst. Koppel "Onderwerp" aan Naam en "Meldingsdatum" aan Datum melding. Laat "Melder" en "Afdeling" op niet importeren staan.             | [ ]           |             |
| 19  | Koppel een ja/nee-kolom (bijvoorbeeld "Melding AP") aan een datumveld (bijvoorbeeld "Datum melding AP"). Controleer dat de vraag verschijnt welke datum bij "ja" hoort, met de keuzes "Datum van de import" en "Vaste datum". | [ ]           |             |
| 20  | Kies "Vaste datum", vul een datum in en controleer dat na de import de rijen met "ja" die datum hebben en rijen met "nee" het veld leeg laten.                                                                                  | [ ]           |             |

---

## 4. Proefdraaien

*Bron: handleiding, kop "Proefdraaien".* Proefdraaien controleert alle rijen zonder iets op te slaan.

| Nr  | Testactie                                                                                                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 21  | Noteer het aantal datalekken in het register. Upload `03-datalekken-met-fouten.xlsx` (register Datalekken), laat de voorgestelde mapping staan en klik "Proefdraaien".                                                                | [ ]           |             |
| 22  | Controleer de samenvatting: 2 rijen passen, 3 rijen hebben aandacht nodig.                                                                                                                                                             | [ ]           |             |
| 23  | Controleer dat per probleemrij het rijnummer en een reden staan: een verplicht veld leeg (Naam), en tweemaal een kolom met een waarde die niet als Ja/nee respectievelijk Datum gelezen kan worden.                                    | [ ]           |             |
| 24  | Controleer dat het aantal datalekken in het register onveranderd is.                                                                                                                                                                   | [ ]           |             |
| 25  | Zet de kolom "Gemeld aan de autoriteit persoonsgegevens (AP)" op niet importeren en draai opnieuw proef. Controleer dat de rij met "misschien" nu wél past (3 passen, 2 aandacht).                                                    | [ ]           |             |

---

## 5. Importeren

*Bron: handleiding, kop "Importeren".*

| Nr  | Testactie                                                                                                                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 26  | Upload `01-datalekken-schoon.xlsx` (register Datalekken) en klik "Importeren". Controleer de melding "3 rijen geïmporteerd" en het scherm "Import afgerond".                                                                                | [ ]           |             |
| 27  | Open het datalekregister en controleer dat de drie datalekken erin staan met status "Gemeld" en een eigen nummer.                                                                                                                             | [ ]           |             |
| 28  | Open "Mail met bijlage naar verkeerde ontvanger". Controleer: type Definitief; Datum melding 04-03-2026 en Datum ontdekking 03-03-2026 (exact deze dagen, niet een dag eerder); gemeld aan AP ja, aan FG ja, aan betrokkene nee.             | [ ]           |             |
| 29  | Controleer bij datzelfde datalek dat "Categorieën van persoonsgegevens" drie losse waarden bevat (Naam, E-mailadres, Adres en woonplaats) en dat de samenvatting en maatregelen als tekst zijn overgenomen, inclusief het accent in "cliëntgegevens". | [ ]           |             |
| 30  | Importeer `03-datalekken-met-fouten.xlsx` met de standaardmapping. Controleer dat er 2 rijen geïmporteerd zijn en dat het resultaatscherm meldt dat 3 rijen zijn overgeslagen.                                                              | [ ]           |             |
| 31  | Controleer dat de probleemrijen inderdaad niet in het register staan en dat de twee goede rijen ("Correcte rij", "Tweede correcte rij") er wel staan.                                                                                        | [ ]           |             |
| 32  | Importeer `01-datalekken-schoon.xlsx` nogmaals. Controleer dat de drie datalekken een tweede keer worden aangemaakt: dit bestand heeft geen bronkenmerk, dus OpenVWR kan de rijen niet herkennen. Verwijder de dubbelen daarna.               | [ ]           |             |
| 32a | Maak in Excel een kopie van `01-datalekken-schoon.xlsx` waarin de naam van de eerste rij 300 tekens lang is, en importeer die. Controleer dat het resultaatscherm 2 geïmporteerde rijen meldt en onder "Niet opgeslagen" rij 1 met een reden toont. | [ ]           |             |
| 33  | Importeer `02-zenya-vim-export.xlsx` met de mapping uit stap 18 en controleer dat de drie meldingen in het register staan en dat op de detailpagina en in het overzicht het importnummer (VIM-2026-…) zichtbaar is.                        | [ ]           |             |
| 34  | Importeer `02-zenya-vim-export.xlsx` nogmaals met dezelfde mapping. Controleer dat er géén dubbelen ontstaan en dat het resultaatscherm meldt dat 3 rijen al eerder waren geïmporteerd en zijn overgeslagen.                                  | [ ]           |             |
| 35  | Controleer op de detailpagina van een geïmporteerde VIM-melding dat de naam van de melder en de afdeling nergens in het record voorkomen.                                                                                                    | [ ]           |             |
| 36  | Controleer dat een geïmporteerd datalek gewoon te bewerken is en het normale proces (wijzigen, melden bij AP, afronden) doorloopt zoals een handmatig aangemaakt datalek.                                                                    | [ ]           |             |

---

## 6. Mapping bewaren en hergebruiken

*Bron: handleiding, kop "Importeren", laatste alinea.*

| Nr  | Testactie                                                                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 37  | Upload `02-zenya-vim-export.xlsx`, stel de mapping uit stap 18 in, vul bij "Mapping bewaren voor hergebruik" de naam "Zenya VIM-export" in en importeer.                                                                        | [ ]           |             |
| 38  | Klik "Opnieuw beginnen" en upload hetzelfde bestand opnieuw. Controleer dat "Bekende indeling herkend" verschijnt met de profielnaam, en dat de mapping (inclusief "niet importeren" voor Melder en Afdeling) al is ingevuld.   | [ ]           |             |
| 39  | Controleer dat de herkende kolommen ingeklapt staan onder "Uit opgeslagen profiel" en dat openklappen en aanpassen nog mogelijk is.                                                                                            | [ ]           |             |
| 40  | Maak in Excel een kopie van `02-zenya-vim-export.xlsx` met één extra kolom "Locatie" en upload die. Controleer dat het profiel nog steeds herkend wordt en dat alleen de nieuwe kolom nog een keuze vraagt.                     | [ ]           |             |
| 41  | Pas de mapping aan, bewaar opnieuw onder dezelfde naam en controleer dat het oude profiel niet overschreven is maar een nieuwe versie ontstaat (bij twijfel: laat een ontwikkelaar de tabel `import_mapping_profiles` nakijken). | [ ]           |             |
| 42  | Log in bij de tweede organisatie en upload `02-zenya-vim-export.xlsx`. Controleer dat het profiel van de eerste organisatie **niet** herkend wordt.                                                                              | [ ]           |             |

---

## 7. Koppelingen en opzoeklijsten (AVG-verwerkingen)

*Bron: handleiding, koppen "Koppelingen naar verwerkers en systemen" en "Opzoeklijsten".* Gebruik het register **AVG Verantwoordelijke Verwerkingen**.

| Nr  | Testactie                                                                                                                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 43  | Maak vooraf handmatig een verwerker "Firma A" aan. Upload `05-verwerkingen-met-verwerkers.csv` met register AVG Verantwoordelijke Verwerkingen.                                                                                                | [ ]           |             |
| 44  | Koppel "Verwerker" aan Verwerkers (groep Koppelingen), "E-mail verwerker" aan "Verwerkers — E-mail", "Postcode verwerker" en "Plaats verwerker" aan de bijbehorende adresvelden van Verwerkers, "Systeem" aan Systemen en "Dienst" aan Dienst (groep Opzoeklijsten). | [ ]           |             |
| 45  | Klik "Importeren". Controleer dat 3 verwerkingen zijn aangemaakt zonder versie: ze doorlopen het goedkeuringsproces pas als iemand handmatig een versie aanmaakt.                                                                                     | [ ]           |             |
| 46  | Controleer onder "Gekoppeld op afwijkende schrijfwijze" dat "Firma A B.V." is gekoppeld aan de bestaande "Firma A", en dat er in het verwerkersregister maar één Firma A staat.                                                              | [ ]           |             |
| 47  | Controleer onder "Nieuw aangemaakt" dat "Zorggroep Noord" en "Firma B" als nieuwe verwerkers zijn aangemaakt, en de systemen "Salarispakket", "HR-systeem" en "ECD" als nieuwe systemen.                                                     | [ ]           |             |
| 48  | Open de verwerking "Cliëntregistratie" en controleer dat er twee verwerkers gekoppeld zijn en dat elk zijn eigen e-mailadres en adres heeft (het tweede e-mailadres hoort bij de tweede naam).                                                | [ ]           |             |
| 49  | Open de opzoeklijst Dienst en controleer dat "Bedrijfsvoering" en "Zorg" zijn toegevoegd en dat de verwerkingen eraan gekoppeld zijn.                                                                                                        | [ ]           |             |
| 50  | Maak handmatig een tweede verwerker "Firma B" aan (dubbel) en importeer het bestand nogmaals. Controleer dat het resultaatscherm onder "Meerdere records met dezelfde naam" meldt dat "Firma B" twee keer voorkomt.                            | [ ]           |             |
| 51  | Upload `02-zenya-vim-export.xlsx` (register Datalekken), voeg in Excel vooraf een kolom "Verwerking" toe met een naam die niet bestaat, koppel die aan AVG Verantwoordelijke Verwerkingen en importeer. Controleer dat de verwerking **niet** wordt aangemaakt en onder "Niet gevonden koppelingen" staat. | [ ]           |             |

---

## 8. Robuustheid en grenzen

| Nr  | Testactie                                                                                                                                                                                                                  | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 52  | Upload een Excel-bestand met ongeveer 2.000 rijen (kopieer de rijen uit `01-datalekken-schoon.xlsx`). Controleer dat analyse, proefdraaien en importeren binnen redelijke tijd afronden en dat de browser niet vastloopt.  | [ ]           |             |
| 53  | Upload een bestand groter dan de toegestane maximumgrootte en controleer dat dit met een nette melding geweigerd wordt.                                                                                                     | [ ]           |             |
| 54  | Zet in een Excel-bestand een formule in een cel (bijvoorbeeld `=1+1`) en een cel die begint met `=HYPERLINK(...)`. Controleer dat na import alleen de tekstwaarde in OpenVWR staat en dat de export naar Excel daarna geen actieve formule bevat. | [ ]           |             |
| 55  | Sla `04-datalekken.csv` in Excel op als CSV met puntkomma als scheidingsteken en upload die. Controleer dat de kolommen apart herkend worden. Upload daarna een CSV in Windows-codering (ANSI) met accenten; een nette foutmelding is acceptabel, verminkte tekst niet. | [ ]           |             |
| 56  | Ververs de browserpagina midden in de stap "Mapping controleren". Controleer dat het scherm terugkeert naar de beginstap zonder foutmelding en dat er niets half is geïmporteerd.                                          | [ ]           |             |
