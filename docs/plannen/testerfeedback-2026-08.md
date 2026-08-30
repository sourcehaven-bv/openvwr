# Plan: testerfeedback augustus 2026

Uitwerking van de testerfeedback naar losse PR's, gegroepeerd per onderwerp.
Elke PR is door één teamlid op te pakken in een eigen worktree.

## Uitgangspunten

- **Basis-branch: `origin/main`.** Niet `develop`: die loopt 82 commits achter en
  alle recente feature-branches vertakken van `main`. PR's richten zich op `main`.
- **Feature flags: globaal via config/env**, in de stijl van `config/static-website.php`.
  Eén deployment bedient één tenant, dus per-tenant kolommen zijn hier niet nodig.
- **Verificatie per PR: Pest-tests + het betreffende scherm één keer handmatig nalopen.**
  Juist bij teksten en handleiding-items zeggen tests alleen te weinig.
- Elke PR is los reviewbaar en los te mergen. Waar volgorde uitmaakt staat dat er expliciet bij.

## Niet oppakken

**Foutief e-mailadres toont toch "login informatie verzonden"** — dit blijft zoals het is.
Een andere melding bij een onbekend adres maakt user enumeration mogelijk: een aanvaller kan
dan uitproberen wie er in het systeem zit. Bewust security-gedrag, geen bug.

---

## PR 1 — Nette Nederlandse melding bij een verlopen loginlink

**Feedback:** "Wanneer ik een oude mail open krijg ik de melding no token found, dit moet netjes in het Nederlands."

`PasswordlessLoginController` gooit `PasswordlessLoginException` met Engelse ontwikkelaarsteksten
(`'no token found'`, `'invalid token'`, `'no organisation found'`), en die string wordt
rechtstreeks als Filament-notificatie aan de gebruiker getoond.

**Bestanden**
- `src/cms/app/Http/Controllers/Authentication/PasswordlessLoginController.php:85,94,100`
- `src/cms/resources/lang/nl/auth.php`

**Aanpak**
- Voeg vertaalsleutels toe aan `auth.php`, bijvoorbeeld `login_link_expired`,
  `login_link_invalid`, `login_no_organisation`.
- Toon de vertaling in de notificatie, niet de exception-message. De exception-message
  blijft Engels voor logging/audit — die scheiding is bewust.
- Meest voorkomende geval is een verlopen link. Formuleer die tekst als uitleg
  plús vervolgstap ("Deze loginlink is verlopen. Vraag een nieuwe login-e-mail aan."),
  niet als kale foutmelding.
- Alle drie de gevallen meenemen, niet alleen `no token found`.

**Verificatie:** test per foutpad dat de vertaalde tekst getoond wordt; handmatig een
verlopen link openen.

---

## PR 2 — Feature flag: publiceren uit de UI

**Feedback:** "Publiceren kan volgens mij helemaal weg toch? Waarom staat dit er nog bij?"

De publicatiefunctionaliteit blijft in het systeem, maar wordt verborgen wanneer de tenant
niet publiceert. Deze PR introduceert het config-bestand dat PR 3 en PR 4 ook gebruiken.

**Bestanden**
- Nieuw: `src/cms/config/features.php` (patroon: `config/static-website.php`)
- `src/cms/app/Filament/Resources/AvgResponsibleProcessingRecordResource/…Form.php` (wizardstap `step_publish`)
- `…FormSchemas.php` / `…InfolistSchemas.php` (`getPublish()`)
- `src/cms/app/Filament/Tables/Actions/GoToStaticWebsiteAction.php`
- `src/cms/app/Filament/Forms/Components/Section/StaticWebsiteCheckSection.php`
- `src/cms/app/Filament/Infolists/Components/Section/StaticWebsiteCheckSection.php`
- `src/cms/app/Filament/Infolists/Components/SnapshotUrlEntry.php`

**Aanpak**
- `config/features.php` met `'publishing' => env('FEATURE_PUBLISHING', true)`.
  Default `true` zodat bestaande deployments niets merken.
- Verberg bij `false`: de wizardstap "Publiceren", de publicatievelden in form én infolist,
  en de knoppen/secties naar de statische website.
- **Alleen de UI verbergen.** Niets weggooien aan model-, snapshot- of exportkant.
- `.env.example` bijwerken zodat de vlag vindbaar is.
- Let op: de wizardstappen zitten ook in `onePageForm()` — beide varianten afdekken.

**Verificatie:** tests met de vlag aan én uit; beide standen handmatig bekijken.

---

## PR 3 — Feature flag: WPG-register uit de UI

**Feedback:** "WPG verantwoordelijke zou er toch uit gaan?"

**Volgorde:** na PR 2, want die levert `config/features.php`.

WPG is een compleet registertype (eigen resource, model, goals, services), dus dit is
puur navigatie/toegang verbergen — niets verwijderen.

**Bestanden**
- `src/cms/config/features.php`
- `src/cms/app/Filament/Resources/WpgProcessingRecordResource.php`
- `src/cms/app/Filament/Resources/WpgProcessingRecordServiceResource.php`
- eventuele WPG-ingangen in navigatie/dashboard

**Aanpak**
- `'wpg' => env('FEATURE_WPG', true)`.
- Verberg via `shouldRegisterNavigation()` én blokkeer `canViewAny()`, zodat de
  route niet alleen uit het menu verdwijnt maar ook niet direct benaderbaar is.
- Controleer of WPG ergens in overzichten, filters of exports opduikt.

**Verificatie:** tests dat de resource met vlag uit niet in navigatie zit én niet
via URL bereikbaar is.

---

## PR 4 — Feature flag: geen publiek/privé-onderscheid zonder publiceren

**Feedback:** "Als een Tenant niet aan publiceren doet, moet er dan nog onderscheid gemaakt
worden tussen publiek of private gegevens in het overzicht?"

**Volgorde:** na PR 2.

**Aanpak**
- Hergebruik de `publishing`-vlag uit PR 2; geen aparte vlag.
- Staat publiceren uit, dan verdwijnt de publiek/privé-bewoording uit de overzichten
  en wordt alles als privé behandeld.
- **De programmatuur blijft ongewijzigd** — alleen de bewoording en de kolom/badge
  verdwijnen uit beeld. Datamodel niet aanpassen.

**Openstaand punt:** even samen vaststellen op welke schermen dit onderscheid nu
precies zichtbaar is, zodat er niets over het hoofd gezien wordt.

---

## PR 5 — Verplichte velden meteen melden, niet pas bij opslaan

**Feedback:** "Wanneer je iets leeg laat maar op volgende drukt is er niks aan de hand,
maar wanneer ik het wil opslaan krijg ik ineens te zien dat het verplichte velden zijn."

**Oorzaak gevonden:** de wizard staat op `->skippable()`
(`AvgResponsibleProcessingRecordResourceForm.php:53`). Daardoor slaat Filament de
per-stap-validatie over en komen alle fouten pas bij opslaan naar boven — vaak op
stappen die de gebruiker allang voorbij is.

**Aanpak: verplicht pas bij versie aanmaken.**

Concept opslaan mag altijd, ook halfaf — dat blijft precies zoals de handleiding het belooft.
Verplichte velden worden pas afgedwongen op het moment dat er een versie wordt aangemaakt,
want dat is het moment waarop een verwerking het goedkeuringsproces in gaat en compleet moet zijn.

Daarmee verdwijnt de tegenstrijdigheid: opslaan zeurt nooit, en het moment waarop het
wél moet kloppen is een bewuste handeling met een duidelijke reden.

**Aanpak**
- Haal de verplicht-validatie weg bij opslaan; `skippable()` blijft staan.
- Verplaats die validatie naar het aanmaken van een versie
  (`app/Filament/Actions/SnapshotTransition/EstablishAction.php` en de versie-aanmaakflow).
- Blokkeert een versie-aanmaak, benoem dan concreet wélke velden op wélke stap ontbreken,
  met een sprong naar de betreffende stap. Niet alleen "er zijn verplichte velden".
- Overweeg op de detailpagina te tonen of een verwerking klaar is voor een versie,
  zodat dat niet pas bij de poging blijkt.

**Raakvlak met PR 10:** dit zit dicht tegen "opslaan vs. versie aanmaken" aan. Deze PR maakt
dat onderscheid juist betekenisvol — opslaan is een concept, versie aanmaken is het echte moment.
Goed om PR 10 hierop af te stemmen.

**Grootste ingreep van deze set.** Raakt alle registertypes (AVG verantwoordelijke, AVG
verwerker, WPG, algoritmes, datalekken) en de snapshotflow. Kandidaat om als eerste te starten.

---

## PR 6 — Handleiding: loginflow kloppend maken

**Feedback:** "Bij de handleiding klopt de login flow niet, de pagina die forceert om de
authenticator in te stellen wordt niet genoemd of als afbeelding getoond."

De screenshots zijn al geautomatiseerd: `tools/screenshots/` (met `capture.mjs`,
`annotate.js` en een README) staat gewoon op `main`. Nieuwe afbeeldingen dus via dat
gereedschap genereren, niet handmatig knippen.

**Aanpak**
- De OTP/authenticator-instelpagina beschrijven in de loginflow.
- Bijbehorende screenshot toevoegen via `tools/screenshots`, zodat stijl en annotaties
  gelijk blijven aan de rest van de handleiding.
- PDF opnieuw genereren met `make handleiding` in `docs/handleiding/`.

**Twee kleine openstaande branches** (beide optioneel, geen blokkade):
- `origin/docs/handleiding-screenshots` — 6 commits die de bestaande tooling verfijnen:
  voegt `ScreenshotSeeder.php` toe en genereert 12 afbeeldingen opnieuw. Handig om te
  landen vóór er nieuwe screenshots bij komen, anders wijken de nieuwe qua data af.
- `origin/fix/handleiding-incomplete-text-fix` — 1 commit, één regel tekst + herbouwde PDF.
  Los te mergen wanneer het uitkomt.

---

## PR 7 — Handleiding: opmaak en koppen

**Feedback:** "Niet alle kopjes in de handleiding zijn netjes ingedeeld zie bijv kopje 4.2
dit moet eigenlijk op de volgende pagina."

**Volgorde:** na PR 6 — beide raken dezelfde bestanden en de herbouwde PDF, dus
tegelijk werken geeft gegarandeerd een conflict op het PDF-binary.

`00_config.md` forceert al een pagina-einde vóór elke `\section` (`\renewcommand\section`),
maar `##`-subsecties (zoals 4.2) mogen midden op een pagina beginnen en breken daardoor lelijk af.

**Aanpak**
- Voorkom losse koppen onderaan een pagina (`\needspace` of een vergelijkbare
  LaTeX-oplossing in `00_config.md`), zodat het generiek geldt in plaats van
  handmatige pagina-einden per kop.
- Handmatige `\newpage` alleen waar het echt niet anders kan.
- Hele PDF nalopen op afgebroken koppen, niet alleen 4.2.

---

## PR 8 — Handleiding en helpteksten universeler maken

**Feedback:** meerdere punten samen —
- "De handleiding is niet aangepast op de workflow voor NVZ", met termen als mandaathouder
  en goedkeuringsproces, en rollen als invoerder "die zij helemaal niet hebben"
- "Bij naam verwerking Primair / Secundair staat in de i allemaal shiit wat voor ministeries
  geldt.. en niet voor NVZ boeiend is"

**Toelichting:** de features zélf blijven — mandaathouders, goedkeuringsproces en de rollen
zitten ook bij NVZ in het systeem. Het gaat om de *teksten*, die nu ministerie-specifiek zijn.

**Bestanden**
- `src/cms/resources/lang/nl/general.php:16` — `data_collection_source_help`.
  Deze tekst noemt letterlijk "het ministerie", "concernniveau" en "het Rijksportaal".
- `docs/handleiding/*.md` — waar de handleiding een ministerie-inrichting veronderstelt

**Aanpak**
- Herschrijf `data_collection_source_help` naar organisatie-neutraal Nederlands:
  leg uit wat primair en secundair betekenen zonder ministerie-voorbeelden. Fors inkorten —
  de huidige tekst is een muur tekst in een tooltip.
- Loop de handleiding na op ministerie-specifieke aannames.
- Rollen die de tenant niet gebruikt: benoemen als optioneel, niet als vaste stap.

Het veld blijft dus gewoon voor iedereen zichtbaar; alleen de toelichting wordt neutraal
en korter. Geen feature flag hier.

---

## PR 9 — Tekst "Hoofdverwerking" corrigeren

**Feedback:** 'De tekst onder hoofdverwerking kan beter: deze verschijnt daar in de tabel
"Subverwerkingen" naar deze wordt vervolgens weergegeven in de tabel "Subverwerkingen"'

**Bestand:** `src/cms/resources/lang/nl/general.php:34` — `parent_hint_icon_text`

**Kanttekening:** de tekst op `main` leest inmiddels correct ("Bij de hoofdverwerking zijn
alle subverwerkingen te vinden in de tabel \"Subverwerkingen\" onderaan deze pagina").
De gemelde dubbeling zie ik daar niet terug. Wie dit oppakt: eerst reproduceren op de
omgeving waar de tester zat. Mogelijk is dit al opgelost, of zit het in een andere tekst.
Klein en snel — daarom apart gehouden.

---

## PR 10 — (nog geen PR) UX-doorloop

**Feedback:** "Ik denk dat we een keer moeten kijken naar het concept wijziging opslaan en
versie aanmaken.. het voelt niet vanzelfsprekend" en "een aantal dingen die qua
gebruikerservaring net ff beter kunnen".

Dit is geen PR maar een sessie. Voorstel: samen doorlopen en er een concrete issuelijst van
maken, waarna elk issue een eigen kleine PR wordt.

Twee dingen om alvast op de agenda te zetten:
- **Opslaan vs. versie aanmaken.** Nu twee losse handelingen. De handleiding legt uit
  waaróm (`03_goedkeuringsproces.md:41`: een vastgestelde versie is niet meer aanpasbaar),
  maar dat is niet zichtbaar in de UI. Denkrichting: na opslaan van een gewijzigde
  verwerking actief aanbieden om een versie aan te maken.
- **Vindbaarheid van het goedkeuringsproces** — wanneer is iets "klaar"?

---

## Werkwijze voor het team

Elk teamlid werkt in een eigen worktree, vertakt van `origin/main`:

```bash
git fetch origin && git worktree add ../openvwr-pr1 -b fix/nl-login-token-melding origin/main
```

Branchnamen: `fix/nl-login-token-melding`, `feat/feature-flag-publiceren`,
`feat/feature-flag-wpg`, `feat/publiek-prive-achter-vlag`, `fix/verplichte-velden-wizard`,
`docs/handleiding-loginflow`, `docs/handleiding-opmaak`, `docs/teksten-universeel`,
`fix/hoofdverwerking-tekst`.

Per PR:
1. Kleine, losse commits met korte commitmessages.
2. Pest-tests voor het gewijzigde gedrag; static analysis groen.
3. Het betreffende scherm één keer handmatig nalopen.
4. PR tegen `main`, met in de omschrijving het feedbackpunt waar het over gaat.

**Parallel op te pakken:** PR 1, PR 2, PR 5, PR 6, PR 8, PR 9.
**Wachten op iets anders:** PR 3 en PR 4 (na PR 2), PR 7 (na PR 6).
**Eerst bespreken:** PR 10 (gezamenlijke UX-doorloop).

PR 5 is de grootste en raakt alle registertypes — die het eerst laten starten.
