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

## 3. Goedkeuringsproces

*Bron: hoofdstuk "Goedkeuringsproces".* Een versie doorloopt de statussen "In review" → "Goedgekeurd" → "Vastgesteld", of wordt "Vervallen".

### 3.1 Versie aanmaken en Mandaathouders koppelen

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                                                                | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 24  | Open een verwerking (of algoritme/datalek) en klik rechtsboven op "Versie aanmaken"; controleer dat de nieuwe versie de status "In review" krijgt.                                                                                                       | [ ]           |             |
| 25  | Sla een verwerking op met ontbrekende verplichte velden en controleer dat dit zonder foutmelding lukt; klik vervolgens op "Versie aanmaken" en controleer dat nu pas de ontbrekende verplichte velden getoond worden, inclusief bij welke stap ze horen. | [ ]           |             |
| 26  | Controleer dat de nieuwe versie zichtbaar is onderaan de pagina, op het tabblad "Versies".                                                                                                                                                               | [ ]           |             |
| 27  | Open de versie-detailpagina, klik op "Ondertekeningen", klik op "Mandaathouders toevoegen", selecteer een of meer Mandaathouders en klik op "Toevoegen".                                                                                                 | [ ]           |             |
| 28  | Controleer dat Privacy Officers automatisch een e-mail ontvangen zodra een nieuwe versie is aangemaakt (tenzij ze deze notificatie hebben uitgezet, zie 5.9).                                                                                            | [ ]           |             |
| 29  | Probeer de inhoud van een reeds aangemaakte versie te wijzigen en controleer dat dit niet mogelijk is; alleen de status kan nog worden aangepast, en alleen door een Privacy Officer.                                                                    | [ ]           |             |

### 3.2 Goedkeuren

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 30  | Open het overzicht van alle versies via het navigatiemenu en controleer dat gesorteerd en gefilterd kan worden op Entiteit-type, Naam versie, Versienummer en Status. | [ ]           |             |
| 31  | Filter het versie-overzicht op status "In review" en controleer dat dit alle nieuw aangemaakte, nog te beoordelen versies toont.                                      | [ ]           |             |
| 32  | Selecteer een versie met status "In review" en klik rechtsboven op "Goedkeuren"; controleer dat de status wijzigt naar "Goedgekeurd".                                 | [ ]           |             |

### 3.3 Akkoord geven

**Beschikbaar voor**: Mandaathouder. Alleen van toepassing als de organisatie met Mandaathouders werkt.

| Nr  | Testactie                                                                                                                                                 | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 33  | Log in als Mandaathouder die aan een versie is gekoppeld, open de versie-detailpagina en klik onderaan op "Akkoord"; controleer dat dit wordt vastgelegd. | [ ]           |             |
| 34  | Klik als Mandaathouder in plaats daarvan op "Niet akkoord" en controleer dat een notitie achtergelaten kan worden.                                        | [ ]           |             |

### 3.4 Vaststellen

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 35  | Filter het versie-overzicht op status "Goedgekeurd" en controleer dat zichtbaar is of er voldoende ondertekeningen (akkoorden) van Mandaathouders zijn.                       | [ ]           |             |
| 36  | Selecteer een versie met status "Goedgekeurd" (met voldoende akkoorden, indien van toepassing) en klik op "Vaststellen"; controleer dat de status wijzigt naar "Vastgesteld". | [ ]           |             |

---

## 4. Beheer

*Bron: hoofdstuk "Beheer".*

### 4.1 Gebruikers

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 37  | Nodig via de knop rechtsboven de gebruikerstabel een nieuwe gebruiker uit en controleer dat deze een welkomstmail met een link naar OpenVWR ontvangt. | [ ]           |             |
| 38  | Klik op een bestaande gebruiker, wijzig de rol(len) en sla op; controleer dat de wijziging is doorgevoerd.                                            | [ ]           |             |
| 39  | Verwijder een gebruiker met de rode knop rechtsboven en controleer dat deze gebruiker niet meer kan inloggen.                                         | [ ]           |             |

### 4.2 Bewaartermijnen

**Beschikbaar voor**: Invoerder, (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 40  | Kies bij een categorie betrokkenen (of bij persoonsgegevens in een DPIA) een bewaartermijn uit de keuzelijst, als deze niet leeg is.                                                        | [ ]           |             |
| 41  | Kies de optie "Overig (zelf invullen)" en controleer dat een tekstveld verschijnt waarin zowel de duur als de grondslag beschreven kunnen worden.                                           | [ ]           |             |
| 42  | Controleer bij een lege opzoeklijst Bewaartermijnen dat de keuzelijst niet getoond wordt en er altijd vrije tekst wordt ingevuld.                                                           | [ ]           |             |
| 43  | Wijzig of verwijder een waarde in de opzoeklijst Bewaartermijnen en controleer dat reeds vastgelegde verwerkingen waarin die termijn is ingevuld, ongewijzigd blijven (regressietest).      | [ ]           |             |
| 44  | Voeg als (Chief) Privacy Officer een nieuwe, veelgebruikte termijn toe aan de opzoeklijst Bewaartermijnen en controleer dat deze bij de eerstvolgende verwerking met één klik te kiezen is. | [ ]           |             |

---

## 5. Overige functies

*Bron: hoofdstuk "Overige Functies".*

### 5.1 Import

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                  | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 45  | Importeer een zip-bestand zoals geëxporteerd vanuit het AVG Register Rijksoverheid en controleer dat de gegevens correct worden ingelezen. | [ ]           |             |
| 46  | Vul na een import handmatig ontbrekende gegevens aan bij een geïmporteerde verwerking en sla op.                                           | [ ]           |             |

### 5.2 Export

**Beschikbaar voor**: (Chief) Privacy Officer, Functionaris Gegevensbescherming.

| Nr  | Testactie                                                                                                                                                                        | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 47  | Klik boven de overzichtstabel van een register op de exportknop en exporteer naar `.csv`; controleer de inhoud van het bestand.                                                  | [ ]           |             |
| 48  | Exporteer hetzelfde register naar `.xlsx` en controleer de inhoud van het bestand.                                                                                               | [ ]           |             |
| 49  | Controleer dat na afronding van de export een notificatie rechtsboven in het scherm verschijnt, en dat de link naar het bestand terug te vinden is in het notificatie-overzicht. | [ ]           |             |
| 50  | Filter een overzicht op een label en exporteer; controleer dat het bestand alleen de gefilterde regels bevat, inclusief een kolom "Labels".                                      | [ ]           |             |

### 5.3 Notificaties

**Beschikbaar voor**: iedereen die e-mails uit het portaal ontvangt.

| Nr  | Testactie                                                                                                                                                                                                                                                            | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 51  | Open "Profiel" > "Instellingen", blok "Notificaties", en controleer dat alle notificaties standaard aan staan.                                                                                                                                                       | [ ]           |             |
| 52  | Vink als Chief privacy officer of Functionaris Gegevensbescherming de notificatie "Een datalek is gemeld bij de Autoriteit Persoonsgegevens" uit; controleer dat hierover geen e-mail meer ontvangen wordt, terwijl de melding zelf in het portaal zichtbaar blijft. | [ ]           |             |
| 53  | Controleer als Privacy Officer dat de notificaties "De notificatiedatum van een document is bereikt", "Een mandaathouder heeft een versie behandeld" en "Een nieuwe versie is aangemaakt" zichtbaar en uit te zetten zijn.                                           | [ ]           |             |
| 54  | Log in met een rol die geen van deze notificatiestromen ontvangt (bijvoorbeeld Raadpleger) en controleer dat het blok "Notificaties" geen irrelevante opties toont.                                                                                                  | [ ]           |             |
| 55  | Log in als Mandaathouder en controleer dat onder "Notificaties Mandaathouder" gekozen kan worden tussen een notificatie per verzoek tot akkoord en een periodiek (wekelijks) overzicht van openstaande verzoeken.                                                    | [ ]           |             |

### 5.4 Opzoeklijsten

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                                                                          | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |:-------------:| ----------- |
| 56  | Open onder "Beheer" > "Opzoeklijsten" de lijst Bewaartermijnen en maak een nieuwe waarde aan.                                                                                                                                                                      | [ ]           |             |
| 57  | Herhaal het aanmaken van een waarde voor elk van de overige opzoeklijsten: Algoritme Statussen, Algoritme Thema's, Document types, AVG Verantwoordelijke Diensten, AVG Verwerking Diensten, WPG Verwerking Diensten, Algoritme Publicatie Categorieën en Functies. | [ ]           |             |
| 58  | Schakel een waarde uit via de tabs boven de tabel en controleer dat deze niet meer in de keuzelijst bij het invoeren verschijnt, maar bij entiteiten waar de waarde al gekozen was, blijft staan.                                                                  | [ ]           |             |
| 59  | Schakel de waarde weer in en controleer dat deze weer beschikbaar is in de keuzelijst.                                                                                                                                                                             | [ ]           |             |
| 60  | Verwijder een waarde uit een opzoeklijst anders dan Bewaartermijnen en controleer dat overal waar deze waarde geselecteerd was, nu niets meer geselecteerd is.                                                                                                     | [ ]           |             |
| 61  | Open de detailpagina van een opzoeklijst-optie en controleer dat een tabel met alle entiteiten waar deze optie geselecteerd is, getoond wordt.                                                                                                                     | [ ]           |             |

---

## 6. Labels

*Bron: hoofdstuk "Labels".*

### 6.1 Labels toekennen

**Beschikbaar voor**: (Chief) Privacy Officer (beheren), Invoerder en Invoerder Datalekken (toekennen), Raadpleger (lezen), Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen).

| Nr  | Testactie                                                                                                                                                                                     | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 62  | Open de detailpagina van een verwerking, klik op het veld "Labels" en voeg een bestaand label toe door te zoeken of te selecteren.                                                            | [ ]           |             |
| 63  | Verwijder een label via het kruisje achter het label en sla de verwerking op; controleer dat het label daadwerkelijk verwijderd is.                                                           | [ ]           |             |
| 64  | Log in als (Chief) Privacy Officer en controleer dat naast het veld "Labels" een "+"-knop zichtbaar is waarmee een nieuw label aangemaakt kan worden zonder de pagina te verlaten.            | [ ]           |             |
| 65  | Log in als Invoerder en controleer dat deze "+"-knop niet zichtbaar is, maar bestaande labels wel toe- en afgekend kunnen worden.                                                             | [ ]           |             |
| 66  | Controleer dat het labelveld op dezelfde manier werkt bij Algoritmes, Datalekken, Verwerkingsverantwoordelijken, Verwerkers, Ontvangers, Systemen/Applicaties, Contactpersonen en Documenten. | [ ]           |             |
| 67  | Ken hetzelfde label toe aan een verwerking en aan het bijbehorende systeem, en controleer dat beide op de detailpagina van het label bij elkaar staan.                                        | [ ]           |             |

### 6.2 Filteren op labels

| Nr  | Testactie                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 68  | Open een overzicht met labels, klik rechtsboven op de filterknop, selecteer een of meer labels onder "Labels" en controleer dat alleen de regels met die labels getoond worden. | [ ]           |             |
| 69  | Klik op "Resetten" en controleer dat het labelfilter wordt opgeheven.                                                                                                           | [ ]           |             |
| 70  | Controleer dat het labelfilter ook werkt in andere overzichten die labels kennen, bijvoorbeeld Systemen/Applicaties of Documenten.                                              | [ ]           |             |

### 6.3 Labels beheren

**Beschikbaar voor**: (Chief) Privacy Officer.

| Nr  | Testactie                                                                                                                                                                                                             | Test Geslaagd | Opmerkingen |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 71  | Open onder "Beheer" > "Labels" het labeloverzicht en controleer dat alle labels van de organisatie getoond worden.                                                                                                    | [ ]           |             |
| 72  | Klik op "Label aanmaken", geef een naam op en controleer dat het label wordt aangemaakt met automatisch een kleur die binnen de organisatie nog niet of het minst gebruikt is.                                        | [ ]           |             |
| 73  | Klik op het potloodje achter een label en wijzig de naam; controleer dat de naamswijziging overal doorwerkt zonder dat koppelingen verloren gaan.                                                                     | [ ]           |             |
| 74  | Wijzig de kleur van een label via het veld "Kleur" en controleer dat er tien kleuren beschikbaar zijn en dat rood niet als optie voorkomt.                                                                            | [ ]           |             |
| 75  | Klik op een label in het overzicht en controleer dat er per type onderdeel (verwerkingen, algoritmes, datalekken, systemen, verwerkers, contactpersonen, documenten, enzovoort) een doorklikbare tabel getoond wordt. | [ ]           |             |
| 76  | Verwijder een label en controleer dat het overal waar het was toegekend verdwenen is, terwijl de onderliggende onderdelen zelf blijven bestaan.                                                                       | [ ]           |             |

---

## 7. Rollen en rechten

*Bron: hoofdstuk "Rollen en Rechten".* Deze sectie toetst de toegangsrechten per rol, in de volgorde waarin ze in de handleiding worden beschreven.

### 7.1 Chief Privacy Officer

| Nr  | Testactie                                                                                                                                          | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 77  | Log in als Chief Privacy Officer en controleer dat verwerkingen, algoritmes en datalekken aangemaakt, gewijzigd en verwijderd kunnen worden.       | [ ]           |             |
| 78  | Controleer dat documenten en verantwoordelijken beheerd kunnen worden.                                                                             | [ ]           |             |
| 79  | Controleer dat versies aangemaakt, goedgekeurd, vastgesteld en vervallen verklaard kunnen worden.                                                  | [ ]           |             |
| 80  | Controleer dat registers geïmporteerd en geëxporteerd kunnen worden.                                                                               | [ ]           |             |
| 81  | Controleer dat opzoeklijsten en labels beheerd kunnen worden.                                                                                      | [ ]           |             |
| 82  | Controleer dat gebruikers uitgenodigd kunnen worden en dat alle rollen, inclusief Chief Privacy Officer en Mandaathouder, toegekend kunnen worden. | [ ]           |             |

### 7.2 Privacy Officer

| Nr  | Testactie                                                                                                                                                                                                                                                              | Test Geslaagd | Opmerkingen |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 83  | Log in als Privacy Officer en controleer dat dezelfde taken als een Chief Privacy Officer uitgevoerd kunnen worden (verwerkingen/algoritmes/datalekken beheren, documenten en verantwoordelijken beheren, goedkeuringsproces, import/export, opzoeklijsten en labels). | [ ]           |             |
| 84  | Controleer bij het uitnodigen van gebruikers dat de rollen Chief Privacy Officer en Mandaathouder niet toegekend kunnen worden.                                                                                                                                        | [ ]           |             |

### 7.3 Invoerder

| Nr  | Testactie                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 85  | Log in als Invoerder en controleer dat verwerkingen en algoritmes aangemaakt, gewijzigd en verwijderd kunnen worden, en dat versies aangemaakt en Mandaathouders eraan gekoppeld kunnen worden. | [ ]           |             |
| 86  | Controleer dat een Invoerder geen versies kan goedkeuren of vaststellen.                                                                                                                        | [ ]           |             |
| 87  | Controleer dat een Invoerder geen registers kan importeren of exporteren, geen opzoeklijsten kan beheren en geen gebruikers kan beheren.                                                        | [ ]           |             |

### 7.4 Invoerder Datalekken

| Nr  | Testactie                                                                                                                                                               | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 88  | Log in als Invoerder Datalekken en controleer dat datalekken, inclusief gekoppelde documenten en verantwoordelijken, aangemaakt, gewijzigd en verwijderd kunnen worden. | [ ]           |             |
| 89  | Controleer dat verwerkingen en algoritmes alleen bekeken kunnen worden, niet aangemaakt of gewijzigd.                                                                   | [ ]           |             |
| 90  | Controleer dat deze rol geen versies kan aanmaken en geen goedkeuringsproces kan uitvoeren.                                                                             | [ ]           |             |

### 7.5 Mandaathouder

| Nr  | Testactie                                                                                                                                   | Test Geslaagd | Opmerkingen |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 91  | Log in als Mandaathouder en controleer dat verwerkingen, algoritmes, documenten en versies bekeken kunnen worden.                           | [ ]           |             |
| 92  | Controleer dat akkoord of niet akkoord gegeven kan worden op versies waarvoor deze Mandaathouder is uitgenodigd, en op geen andere versies. | [ ]           |             |
| 93  | Controleer dat deze rol geen gegevens kan invoeren of wijzigen en geen versies kan goedkeuren of vaststellen.                               | [ ]           |             |

### 7.6 Raadpleger

| Nr  | Testactie                                                                                                                                                                                 | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 94  | Log in als Raadpleger en controleer dat registers, documenten, versies en het goedkeuringsproces bekeken kunnen worden, zonder dat er iets ingevoerd, gewijzigd of verwijderd kan worden. | [ ]           |             |

### 7.7 Functionaris Gegevensbescherming

| Nr  | Testactie                                                                                                                                                                                       | Test Geslaagd | Opmerkingen |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 95  | Log in als Functionaris Gegevensbescherming en controleer dat deze rol dezelfde leesrechten heeft als de Raadpleger.                                                                            | [ ]           |             |
| 96  | Controleer dat een Functionaris Gegevensbescherming opmerkingen kan plaatsen bij een verwerking, en dat deze opmerkingen alleen zichtbaar zijn voor andere Functionarissen Gegevensbescherming. | [ ]           |             |
| 97  | Controleer dat een Functionaris Gegevensbescherming registers kan exporteren naar `.csv` en `.xlsx`.                                                                                            | [ ]           |             |

---

## 8. Aanvullende regressietests

Niet beschreven in de handleiding, maar relevant gezien recente functionaliteit rond de publieke website en publicatiestatus.

| Nr  | Testactie                                                                                                                                                                                                      | Test Geslaagd | Opmerkingen |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------:| ----------- |
| 98  | Schakel de publieke website uit (feature uit) en controleer dat het menu-item voor de publieke websiteboom verdwijnt.                                                                                          | [ ]           |             |
| 99  | Benader met diezelfde feature uitgeschakeld de URL van de publieke websiteboom rechtstreeks, en controleer dat de pagina niet toegankelijk is (verwacht: 403) in plaats van alleen het menu-item te verbergen. | [ ]           |             |
| 100 | Controleer op het dashboard het widget met verlopen of binnenkort verlopende periodieke reviews en documenttermijnen (bijvoorbeeld van een DPIA), en controleer dat de juiste items getoond worden.            | [ ]           |             |
| 101 | Verberg bij een verwerking of algoritme het publiek/privé-onderscheid zonder deze te publiceren, en controleer dat dit onderscheid pas zichtbaar wordt nadat er daadwerkelijk gepubliceerd is.                 | [ ]           |             |

---

**[EINDE TEST]**
