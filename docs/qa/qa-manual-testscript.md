# Testscript OpenVWR — Handmatige QA

Dit testscript ondersteunt handmatige QA van OpenVWR. De volgorde van de secties volgt de hoofdstukken van de handleiding (`docs/handleiding/`, gepubliceerd als `src/cms/public/pdf/openvwr_handleiding.pdf`). Elke regel in een tabel heeft een uniek, doorlopend nummer.

Vink een regel af in de kolom "Test Geslaagd" zodra de stap is uitgevoerd en het verwachte resultaat klopt. Noteer bij een afwijking of bijzonderheid altijd een toelichting in "Opmerkingen", ook als de stap slaagt maar iets opviel.

**Tester:** ________________________ &nbsp;&nbsp; **Datum:** ________________________ &nbsp;&nbsp; **Omgeving/versie:** ________________________

---

## 1. Inloggen en tweefactorauthenticatie

*Bron: hoofdstuk "Welkom", sectie "Inloggen".* Tweefactorauthenticatie (2FA) is verplicht; zonder ingestelde 2FA is de rest van de applicatie niet toegankelijk.

### 1.1 De authenticator instellen

| Nr  | Testactie                                                                                                                                                                                                | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 1   | Log in met een account dat nog geen 2FA heeft ingesteld. Controleer dat je automatisch naar "Mijn profiel" wordt gestuurd en dat de rest van de applicatie niet toegankelijk is totdat 2FA is ingesteld. | [ ]           |             |
| 2   | Klik op "Inschakelen" en controleer dat een QR-code met daaronder een sleutel verschijnt.                                                                                                                | [ ]           |             |
| 3   | Scan de QR-code met een ondersteunde authenticator-app (Microsoft Authenticator, Google Authenticator of FreeOTP) en controleer dat het account daarin verschijnt.                                       | [ ]           |             |
| 4   | Voer, als alternatief voor het scannen, de getoonde sleutel handmatig in de authenticator-app in en controleer dat dit ook werkt.                                                                        | [ ]           |             |
| 5   | Klik op "Bevestigen", vul de zescijferige code in en controleer dat 2FA wordt ingeschakeld en de applicatie daarna toegankelijk is.                                                                      | [ ]           |             |
| 6   | Vul een onjuiste zescijferige code in en controleer dat een foutmelding verschijnt en een nieuwe poging mogelijk is.                                                                                     | [ ]           |             |
| 7   | Klik tijdens het instellen op "Resetten" en controleer dat een nieuwe QR-code met een nieuwe sleutel wordt gegenereerd.                                                                                  | [ ]           |             |

### 1.2 Inloggen met de authenticator

| Nr  | Testactie                                                                                                                                                                           | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 8   | Log met een account waarvoor 2FA al is ingesteld in met het e-mailadres en controleer dat vervolgens een apart scherm voor de zescijferige code verschijnt.                         | [ ]           |             |
| 9   | Vul de actuele zescijferige code uit de authenticator-app in en controleer dat het inloggen slaagt.                                                                                 | [ ]           |             |
| 10  | Log in met een verlopen loginlink (of laat een link verlopen) en controleer dat een Nederlandstalige foutmelding wordt getoond in plaats van een generieke of Engelstalige melding. | [ ]           |             |
| 11  | Reset als beheerder de 2FA van een gebruiker die zijn authenticator kwijt is, en controleer dat deze gebruiker bij de eerstvolgende login het instelproces (1.1) opnieuw doorloopt. | [ ]           |             |

---

## 2. Registers

*Bron: hoofdstuk "Registers".*

### 2.1 Verwerkingsregisters — AVG Verantwoordelijke Verwerkingen, AVG Verwerker Verwerkingen, WPG Verantwoordelijke Verwerkingen

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen). Voer onderstaande controles uit voor **elk van de drie registers**.

| Nr  | Testactie                                                                                                                                              | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 12  | Open het register en controleer dat het overzicht de kolommen Nummer verwerking, Naam verwerking, Status en Periodieke review toont.                   | [ ]           |             |
| 13  | Sorteer het overzicht achtereenvolgens op elk van deze kolommen en controleer dat de sortering klopt.                                                  | [ ]           |             |
| 14  | Klik op "Verwerking aanmaken" en controleer dat het invoerformulier (detailpagina) opent, met rechts een navigatiemenu voor de verschillende domeinen. | [ ]           |             |
| 15  | Sla een verwerking op zonder alle velden in te vullen en controleer dat dit zonder foutmelding lukt.                                                   | [ ]           |             |
| 16  | Klik op een bestaande verwerking in het overzicht en controleer dat de detailpagina met de eerder ingevoerde gegevens opent.                           | [ ]           |             |
| 17  | Leg een relatie met een andere entiteit vast, sla op, en controleer dat de relatie zichtbaar is in de tabellen onderaan de detailpagina.               | [ ]           |             |
| 18  | Klik rechtsboven op de duplicatie-knop en controleer dat een nieuwe verwerking wordt aangemaakt met dezelfde waarden in alle velden.                   | [ ]           |             |

### 2.2 Algoritmes

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen).

| Nr  | Testactie                                                                                                                                  | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 19  | Herhaal de controles 12–18 voor het algoritmeregister en controleer dat het overzicht dezelfde kolommen toont als de verwerkingsregisters. | [ ]           |             |

### 2.3 Datalekken

**Beschikbaar voor**: Invoerder Datalekken, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen).

| Nr  | Testactie                                                                                                                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 20  | Open het datalekregister en controleer dat het overzicht de kolommen Nummer datalek, Naam datalek, Datum melding en "Gemeld aan de AP" toont.                                                                                               | [ ]           |             |
| 21  | Sorteer het overzicht op elk van deze kolommen.                                                                                                                                                                                             | [ ]           |             |
| 22  | Klik op "Datalek aanmaken" en controleer dat het invoerformulier opent.                                                                                                                                                                     | [ ]           |             |
| 23  | Vink bij een datalek aan dat het gemeld is bij de Autoriteit Persoonsgegevens, sla op, en controleer dat de Chief privacy officer(s) en Mandaathouder(s) van de organisatie automatisch een e-mail ontvangen met een link naar het datalek. | [ ]           |             |

---

## 3. DPIA

*Bron: hoofdstuk "DPIA".* Naast de verwerkingsregisters kent OpenVWR een apart onderdeel voor DPIA's, te vinden onder "DPIA" in het navigatiemenu, direct onder de verwerkingsregisters.

### 3.1 Pre-scan DPIA

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen).

| Nr  | Testactie                                                                                                                                                                                                                        | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 24  | Open het Pre-scan DPIA-register en maak een nieuwe Pre-scan DPIA aan; doorloop de stappen Algemeen, Aanleiding, AP-criteria, EDPB-criteria, Doorgifte en Overig, en controleer dat elke stap opgeslagen kan worden.            | [ ]           |             |
| 25  | Vul de criteria zo in dat geen van de AP- of EDPB-criteria van toepassing is, sla op, en controleer dat de berekende uitkomst voor DPIA, DTIA, KIA en IAMA "niet nodig" toont.                                                  | [ ]           |             |
| 26  | Vink minimaal één AP-criterium aan en controleer dat de uitkomst voor DPIA "verplicht" wordt; vink in plaats daarvan precies één EDPB-criterium aan (geen AP-criterium) en controleer dat de uitkomst "aanbevolen" wordt.       | [ ]           |             |
| 27  | Vul bij "Doorgifte" internationale doorgifte buiten de EER in, en bij "Overig" respectievelijk minderjarigen en een algoritme/hoogrisico-AI-toepassing; controleer dat de uitkomst voor DTIA, KIA en IAMA hierop meebeweegt.    | [ ]           |             |
| 28  | Koppel bij "Koppelen" een of meer verwerkingen aan de Pre-scan DPIA en sla op.                                                                                                                                                   | [ ]           |             |
| 29  | Controleer bij een Pre-scan DPIA waarvan de uitkomst voor DPIA "verplicht" of "aanbevolen" is, dat op de detailpagina de knop "DPIA starten" verschijnt; controleer bij een uitkomst "niet nodig" voor alle instrumenten dat deze knop ontbreekt. | [ ]           |             |
| 30  | Klik op "DPIA starten" en controleer dat een nieuwe DPIA wordt aangemaakt met daarin de naam, omschrijving en gekoppelde verwerkingen van de Pre-scan DPIA.                                                                        | [ ]           |             |
| 31  | Controleer dat een Pre-scan DPIA geen knop "Versie aanmaken" heeft en geen status; het is een eenmalige toets zonder goedkeuringsproces.                                                                                        | [ ]           |             |

### 3.2 DPIA

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen).

| Nr  | Testactie                                                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 32  | Open het DPIA-register en maak een nieuwe, losstaande DPIA aan (niet via "DPIA starten"); doorloop de stappen van het formulier tot en met de samenvatting en opmerkingen.                                     | [ ]           |             |
| 33  | Voeg bij de paragraaf "Persoonsgegevens" een gegeven toe, kies een type (gewoon, gevoelig, bijzonder, strafrechtelijk of identificatienummer) en vul de bewaartermijn in op dezelfde manier als bij een verwerking (zie 5.2). | [ ]           |             |
| 34  | Voeg een bijzonder of strafrechtelijk persoonsgegeven, of een identificatienummer, toe en controleer dat daarbij verplicht een uitzonderingsgrond ingevuld moet worden.                                      | [ ]           |             |
| 35  | Voeg bij "Risico's" een risico toe met een inschatting van kans en impact, en koppel er bij "Maatregelen" een maatregel aan.                                                                                  | [ ]           |             |
| 36  | Zorg dat na het nemen van maatregelen een hoog restrisico overblijft, en controleer dat OpenVWR wijst op de verplichte voorafgaande consultatie van de Autoriteit Persoonsgegevens; leg dit vast bij "Consultatie". | [ ]           |             |
| 37  | Vul bij "Review" een hersbeoordelingsdatum in en controleer dat de bijbehorende helptekst wijst op een termijn van maximaal drie jaar.                                                                        | [ ]           |             |
| 38  | Klik op "Versie aanmaken" bij een DPIA en controleer dat deze hetzelfde goedkeuringsproces doorloopt als een verwerking: status "In review", goedkeuren, eventueel akkoord geven, en vaststellen (zie hoofdstuk 4). | [ ]           |             |
| 39  | Dupliceer een DPIA via de duplicatie-knop rechtsboven en controleer dat een nieuwe DPIA wordt aangemaakt met dezelfde waarden in alle velden.                                                                 | [ ]           |             |
| 40  | Ken een label toe aan een DPIA en controleer dat het labelveld op dezelfde manier werkt als bij een verwerking (zie hoofdstuk 7).                                                                             | [ ]           |             |
| 41  | Controleer dat een DPIA niet gepubliceerd kan worden op de publieke website: er is geen publiceeractie of publiek/privé-optie aanwezig, anders dan bij bijvoorbeeld verwerkingen.                             | [ ]           |             |

---

## 4. Goedkeuringsproces

*Bron: hoofdstuk "Goedkeuringsproces".* Een versie doorloopt de statussen "In review" → "Goedgekeurd" → "Vastgesteld", of wordt "Vervallen".

### 4.1 Versie aanmaken en Mandaathouders koppelen

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                                                                | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 42  | Open een verwerking (of algoritme/datalek/DPIA) en klik rechtsboven op "Versie aanmaken"; controleer dat de nieuwe versie de status "In review" krijgt.                                                                                                  | [ ]           |             |
| 43  | Sla een verwerking op met ontbrekende verplichte velden en controleer dat dit zonder foutmelding lukt; klik vervolgens op "Versie aanmaken" en controleer dat nu pas de ontbrekende verplichte velden getoond worden, inclusief bij welke stap ze horen. | [ ]           |             |
| 44  | Controleer dat de nieuwe versie zichtbaar is onderaan de pagina, op het tabblad "Versies".                                                                                                                                                               | [ ]           |             |
| 45  | Open de versie-detailpagina, klik op "Ondertekeningen", klik op "Mandaathouders toevoegen", selecteer een of meer Mandaathouders en klik op "Toevoegen".                                                                                                 | [ ]           |             |
| 46  | Controleer dat Privacy Officers automatisch een e-mail ontvangen zodra een nieuwe versie is aangemaakt (tenzij ze deze notificatie hebben uitgezet, zie 6.3).                                                                                            | [ ]           |             |
| 47  | Probeer de inhoud van een reeds aangemaakte versie te wijzigen en controleer dat dit niet mogelijk is; alleen de status kan nog worden aangepast, en alleen door een Privacy Officer.                                                                    | [ ]           |             |

### 4.2 Goedkeuren

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 48  | Open het overzicht van alle versies via het navigatiemenu en controleer dat gesorteerd en gefilterd kan worden op Entiteit-type, Naam versie, Versienummer en Status. | [ ]           |             |
| 49  | Filter het versie-overzicht op status "In review" en controleer dat dit alle nieuw aangemaakte, nog te beoordelen versies toont.                                      | [ ]           |             |
| 50  | Selecteer een versie met status "In review" en klik rechtsboven op "Goedkeuren"; controleer dat de status wijzigt naar "Goedgekeurd".                                 | [ ]           |             |

### 4.3 Akkoord geven

**Beschikbaar voor**: Mandaathouder. Alleen van toepassing als de organisatie met Mandaathouders werkt.

| Nr  | Testactie                                                                                                                                                 | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 51  | Log in als Mandaathouder die aan een versie is gekoppeld, open de versie-detailpagina en klik onderaan op "Akkoord"; controleer dat dit wordt vastgelegd. | [ ]           |             |
| 52  | Klik als Mandaathouder in plaats daarvan op "Niet akkoord" en controleer dat een notitie achtergelaten kan worden.                                        | [ ]           |             |

### 4.4 Vaststellen

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 53  | Filter het versie-overzicht op status "Goedgekeurd" en controleer dat zichtbaar is of er voldoende ondertekeningen (akkoorden) van Mandaathouders zijn.                       | [ ]           |             |
| 54  | Selecteer een versie met status "Goedgekeurd" (met voldoende akkoorden, indien van toepassing) en klik op "Vaststellen"; controleer dat de status wijzigt naar "Vastgesteld". | [ ]           |             |

---

## 5. Beheer

*Bron: hoofdstuk "Beheer".*

### 5.1 Gebruikers

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 55  | Nodig via de knop rechtsboven de gebruikerstabel een nieuwe gebruiker uit en controleer dat deze een welkomstmail met een link naar OpenVWR ontvangt. | [ ]           |             |
| 56  | Klik op een bestaande gebruiker, wijzig de rol(len) en sla op; controleer dat de wijziging is doorgevoerd.                                            | [ ]           |             |
| 57  | Verwijder een gebruiker met de rode knop rechtsboven en controleer dat deze gebruiker niet meer kan inloggen.                                         | [ ]           |             |

### 5.2 Bewaartermijnen

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 58  | Kies bij een categorie betrokkenen (of bij persoonsgegevens in een DPIA, zie 3.2) een bewaartermijn uit de keuzelijst, als deze niet leeg is.                                               | [ ]           |             |
| 59  | Kies de optie "Overig (zelf invullen)" en controleer dat een tekstveld verschijnt waarin zowel de duur als de grondslag beschreven kunnen worden.                                           | [ ]           |             |
| 60  | Controleer bij een lege opzoeklijst Bewaartermijnen dat de keuzelijst niet getoond wordt en er altijd vrije tekst wordt ingevuld.                                                           | [ ]           |             |
| 61  | Wijzig of verwijder een waarde in de opzoeklijst Bewaartermijnen en controleer dat reeds vastgelegde verwerkingen waarin die termijn is ingevuld, ongewijzigd blijven (regressietest).      | [ ]           |             |
| 62  | Voeg als (Chief) Privacy Officer een nieuwe, veelgebruikte termijn toe aan de opzoeklijst Bewaartermijnen en controleer dat deze bij de eerstvolgende verwerking met één klik te kiezen is. | [ ]           |             |

---

## 6. Overige functies

*Bron: hoofdstuk "Overige Functies".*

### 6.1 Import

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                  | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 63  | Importeer een zip-bestand zoals geëxporteerd vanuit het AVG Register Rijksoverheid en controleer dat de gegevens correct worden ingelezen. | [ ]           |             |
| 64  | Vul na een import handmatig ontbrekende gegevens aan bij een geïmporteerde verwerking en sla op.                                           | [ ]           |             |

### 6.2 Export

**Beschikbaar voor**: (Chief) Privacy Officer, Functionaris Gegevensbescherming.

| Nr  | Testactie                                                                                                                                                                        | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 65  | Klik boven de overzichtstabel van een register op de exportknop en exporteer naar `.csv`; controleer de inhoud van het bestand.                                                  | [ ]           |             |
| 66  | Exporteer hetzelfde register naar `.xlsx` en controleer de inhoud van het bestand.                                                                                               | [ ]           |             |
| 67  | Controleer dat na afronding van de export een notificatie rechtsboven in het scherm verschijnt, en dat de link naar het bestand terug te vinden is in het notificatie-overzicht. | [ ]           |             |
| 68  | Filter een overzicht op een label en exporteer; controleer dat het bestand alleen de gefilterde regels bevat, inclusief een kolom "Labels".                                      | [ ]           |             |

### 6.3 Notificaties

**Beschikbaar voor**: iedereen die e-mails uit het portaal ontvangt.

| Nr  | Testactie                                                                                                                                                                                                                                                            | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 69  | Open "Profiel" > "Instellingen", blok "Notificaties", en controleer dat alle notificaties standaard aan staan.                                                                                                                                                       | [ ]           |             |
| 70  | Vink als Chief privacy officer of Functionaris Gegevensbescherming de notificatie "Een datalek is gemeld bij de Autoriteit Persoonsgegevens" uit; controleer dat hierover geen e-mail meer ontvangen wordt, terwijl de melding zelf in het portaal zichtbaar blijft. | [ ]           |             |
| 71  | Controleer als Privacy Officer dat de notificaties "De notificatiedatum van een document is bereikt", "Een mandaathouder heeft een versie behandeld" en "Een nieuwe versie is aangemaakt" zichtbaar en uit te zetten zijn.                                           | [ ]           |             |
| 72  | Log in met een rol die geen van deze notificatiestromen ontvangt (bijvoorbeeld Raadpleger) en controleer dat het blok "Notificaties" geen irrelevante opties toont.                                                                                                  | [ ]           |             |
| 73  | Log in als Mandaathouder en controleer dat onder "Notificaties Mandaathouder" gekozen kan worden tussen een notificatie per verzoek tot akkoord en een periodiek (wekelijks) overzicht van openstaande verzoeken.                                                    | [ ]           |             |

### 6.4 Opzoeklijsten

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                                                                          | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 74  | Open onder "Beheer" > "Opzoeklijsten" de lijst Bewaartermijnen en maak een nieuwe waarde aan.                                                                                                                                                                      | [ ]           |             |
| 75  | Herhaal het aanmaken van een waarde voor elk van de overige opzoeklijsten: Algoritme Statussen, Algoritme Thema's, Document types, AVG Verantwoordelijke Diensten, AVG Verwerking Diensten, WPG Verwerking Diensten, Algoritme Publicatie Categorieën en Functies. | [ ]           |             |
| 76  | Schakel een waarde uit via de tabs boven de tabel en controleer dat deze niet meer in de keuzelijst bij het invoeren verschijnt, maar bij entiteiten waar de waarde al gekozen was, blijft staan.                                                                  | [ ]           |             |
| 77  | Schakel de waarde weer in en controleer dat deze weer beschikbaar is in de keuzelijst.                                                                                                                                                                             | [ ]           |             |
| 78  | Verwijder een waarde uit een opzoeklijst anders dan Bewaartermijnen en controleer dat overal waar deze waarde geselecteerd was, nu niets meer geselecteerd is.                                                                                                     | [ ]           |             |
| 79  | Open de detailpagina van een opzoeklijst-optie en controleer dat een tabel met alle entiteiten waar deze optie geselecteerd is, getoond wordt.                                                                                                                     | [ ]           |             |

---

## 7. Labels

*Bron: hoofdstuk "Labels".*

### 7.1 Labels toekennen

**Beschikbaar voor**: (Chief) Privacy Officer (beheren), Invoerder en Invoerder Datalekken (toekennen), Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen).

| Nr  | Testactie                                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 80  | Open de detailpagina van een verwerking, klik op het veld "Labels" en voeg een bestaand label toe door te zoeken of te selecteren.                                                            | [ ]           |             |
| 81  | Verwijder een label via het kruisje achter het label en sla de verwerking op; controleer dat het label daadwerkelijk verwijderd is.                                                           | [ ]           |             |
| 82  | Log in als (Chief) Privacy Officer en controleer dat naast het veld "Labels" een "+"-knop zichtbaar is waarmee een nieuw label aangemaakt kan worden zonder de pagina te verlaten.            | [ ]           |             |
| 83  | Log in als Invoerder en controleer dat deze "+"-knop niet zichtbaar is, maar bestaande labels wel toe- en afgekend kunnen worden.                                                             | [ ]           |             |
| 84  | Controleer dat het labelveld op dezelfde manier werkt bij Algoritmes, Datalekken, DPIA's, Pre-scan DPIA's, Verwerkingsverantwoordelijken, Verwerkers, Ontvangers, Systemen/Applicaties, Contactpersonen en Documenten. | [ ]           |             |
| 85  | Ken hetzelfde label toe aan een verwerking en aan het bijbehorende systeem, en controleer dat beide op de detailpagina van het label bij elkaar staan.                                        | [ ]           |             |

### 7.2 Filteren op labels

| Nr  | Testactie                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 86  | Open een overzicht met labels, klik rechtsboven op de filterknop, selecteer een of meer labels onder "Labels" en controleer dat alleen de regels met die labels getoond worden. | [ ]           |             |
| 87  | Klik op "Resetten" en controleer dat het labelfilter wordt opgeheven.                                                                                                           | [ ]           |             |
| 88  | Controleer dat het labelfilter ook werkt in andere overzichten die labels kennen, bijvoorbeeld Systemen/Applicaties of Documenten.                                              | [ ]           |             |

### 7.3 Labels beheren

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 89  | Open onder "Beheer" > "Labels" het labeloverzicht en controleer dat alle labels van de organisatie getoond worden.                                                                                                    | [ ]           |             |
| 90  | Klik op "Label aanmaken", geef een naam op en controleer dat het label wordt aangemaakt met automatisch een kleur die binnen de organisatie nog niet of het minst gebruikt is.                                        | [ ]           |             |
| 91  | Klik op het potloodje achter een label en wijzig de naam; controleer dat de naamswijziging overal doorwerkt zonder dat koppelingen verloren gaan.                                                                     | [ ]           |             |
| 92  | Wijzig de kleur van een label via het veld "Kleur" en controleer dat er tien kleuren beschikbaar zijn en dat rood niet als optie voorkomt.                                                                            | [ ]           |             |
| 93  | Klik op een label in het overzicht en controleer dat er per type onderdeel (verwerkingen, algoritmes, datalekken, DPIA's, systemen, verwerkers, contactpersonen, documenten, enzovoort) een doorklikbare tabel getoond wordt. | [ ]           |             |
| 94  | Verwijder een label en controleer dat het overal waar het was toegekend verdwenen is, terwijl de onderliggende onderdelen zelf blijven bestaan.                                                                       | [ ]           |             |

---

## 8. Rollen en rechten

*Bron: hoofdstuk "Rollen en Rechten".* Deze sectie toetst de toegangsrechten per rol, in de volgorde waarin ze in de handleiding worden beschreven.

### 8.1 Chief Privacy Officer

| Nr  | Testactie                                                                                                                                          | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 95  | Log in als Chief Privacy Officer en controleer dat verwerkingen, algoritmes, datalekken, Pre-scan DPIA's en DPIA's aangemaakt, gewijzigd en verwijderd kunnen worden. | [ ]           |             |
| 96  | Controleer dat documenten en verantwoordelijken beheerd kunnen worden.                                                                             | [ ]           |             |
| 97  | Controleer dat versies aangemaakt, goedgekeurd, vastgesteld en vervallen verklaard kunnen worden.                                                  | [ ]           |             |
| 98  | Controleer dat registers geïmporteerd en geëxporteerd kunnen worden.                                                                               | [ ]           |             |
| 99  | Controleer dat opzoeklijsten en labels beheerd kunnen worden.                                                                                      | [ ]           |             |
| 100 | Controleer dat gebruikers uitgenodigd kunnen worden en dat alle rollen, inclusief Chief Privacy Officer en Mandaathouder, toegekend kunnen worden. | [ ]           |             |

### 8.2 Privacy Officer

| Nr  | Testactie                                                                                                                                                                                                                                                              | Test Geslaagd | Opmerkingen |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 101 | Log in als Privacy Officer en controleer dat dezelfde taken als een Chief Privacy Officer uitgevoerd kunnen worden (verwerkingen/algoritmes/datalekken/DPIA's beheren, documenten en verantwoordelijken beheren, goedkeuringsproces, import/export, opzoeklijsten en labels). | [ ]           |             |
| 102 | Controleer bij het uitnodigen van gebruikers dat de rollen Chief Privacy Officer en Mandaathouder niet toegekend kunnen worden.                                                                                                                                        | [ ]           |             |

### 8.3 Invoerder

| Nr  | Testactie                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 103 | Log in als Invoerder en controleer dat verwerkingen, algoritmes, Pre-scan DPIA's en DPIA's aangemaakt, gewijzigd en verwijderd kunnen worden, en dat versies aangemaakt en Mandaathouders eraan gekoppeld kunnen worden. | [ ]           |             |
| 104 | Controleer dat een Invoerder geen versies kan goedkeuren of vaststellen.                                                                                                                        | [ ]           |             |
| 105 | Controleer dat een Invoerder geen registers kan importeren of exporteren, geen opzoeklijsten kan beheren en geen gebruikers kan beheren.                                                        | [ ]           |             |

### 8.4 Invoerder Datalekken

| Nr  | Testactie                                                                                                                                                               | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 106 | Log in als Invoerder Datalekken en controleer dat datalekken, inclusief gekoppelde documenten en verantwoordelijken, aangemaakt, gewijzigd en verwijderd kunnen worden. | [ ]           |             |
| 107 | Controleer dat verwerkingen, algoritmes, Pre-scan DPIA's en DPIA's alleen bekeken kunnen worden, niet aangemaakt of gewijzigd.                                          | [ ]           |             |
| 108 | Controleer dat deze rol geen versies kan aanmaken en geen goedkeuringsproces kan uitvoeren.                                                                             | [ ]           |             |

### 8.5 Mandaathouder

| Nr  | Testactie                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 109 | Log in als Mandaathouder en controleer dat verwerkingen, algoritmes, DPIA's, documenten en versies bekeken kunnen worden.                    | [ ]           |             |
| 110 | Controleer dat akkoord of niet akkoord gegeven kan worden op versies waarvoor deze Mandaathouder is uitgenodigd, en op geen andere versies. | [ ]           |             |
| 111 | Controleer dat deze rol geen gegevens kan invoeren of wijzigen en geen versies kan goedkeuren of vaststellen.                               | [ ]           |             |

### 8.6 Raadpleger

| Nr  | Testactie                                                                                                                                                                                 | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 112 | Log in als Raadpleger en controleer dat registers, DPIA's, documenten, versies en het goedkeuringsproces bekeken kunnen worden, zonder dat er iets ingevoerd, gewijzigd of verwijderd kan worden. | [ ]           |             |

### 8.7 Functionaris Gegevensbescherming

| Nr  | Testactie                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 113 | Log in als Functionaris Gegevensbescherming en controleer dat deze rol dezelfde leesrechten heeft als de Raadpleger.                                                                            | [ ]           |             |
| 114 | Controleer dat een Functionaris Gegevensbescherming opmerkingen kan plaatsen bij een verwerking, en dat deze opmerkingen alleen zichtbaar zijn voor andere Functionarissen Gegevensbescherming. | [ ]           |             |
| 115 | Controleer dat een Functionaris Gegevensbescherming registers kan exporteren naar `.csv` en `.xlsx`.                                                                                            | [ ]           |             |

---

## 9. Aanvullende regressietests

Niet beschreven in de handleiding, maar relevant gezien recente functionaliteit rond de publieke website en publicatiestatus.

| Nr  | Testactie                                                                                                                                                                                                      | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 116 | Schakel de publieke website uit (feature uit) en controleer dat het menu-item voor de publieke websiteboom verdwijnt.                                                                                          | [ ]           |             |
| 117 | Benader met diezelfde feature uitgeschakeld de URL van de publieke websiteboom rechtstreeks, en controleer dat de pagina niet toegankelijk is (verwacht: 403) in plaats van alleen het menu-item te verbergen. | [ ]           |             |
| 118 | Controleer op het dashboard het widget met verlopen of binnenkort verlopende periodieke reviews en documenttermijnen (bijvoorbeeld van een DPIA), en controleer dat de juiste items getoond worden.            | [ ]           |             |
| 119 | Verberg bij een verwerking of algoritme het publiek/privé-onderscheid zonder deze te publiceren, en controleer dat dit onderscheid pas zichtbaar wordt nadat er daadwerkelijk gepubliceerd is.                 | [ ]           |             |

---

**[EINDE TEST]**
