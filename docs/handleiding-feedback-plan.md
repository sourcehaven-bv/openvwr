# Plan: verwerking feedback op de handleiding

Reactie op de MoSCoW-review van de handleiding (proeftuin NVZ). Per bevinding:
wat er waar is, wat de code zegt, en wat we voorstellen te doen.

Basis: PR #148 (`feat/vaststellen-vervallen`). Dat is bewust, want die PR heeft
de tekst over het goedkeuringsproces al herschreven — zie
[Naslag: versie-indienen](#naslag-versie-indienen).

## Waar de handleiding staat

De handleiding staat niet in markdown-bestanden maar in PHP, onder
`src/cms/app/Manual/`. Twee lagen:

- **Taken** (`Content/TaskContent.php`) — "wat wilt u doen?". Een `Task` heeft
  stappen; een `Step` is één of twee zinnen plus `topicIds` naar de naslag.
- **Naslag** (`Content/Chapters/*.php`) — de canonieke uitleg. Eén `Topic` per
  onderwerp, body is markdown in een heredoc.

Het leidende principe staat in de klassecommentaren: *een uitleg staat precies
één keer*. Taken linken naar naslag in plaats van te herhalen. De backlinks
("Gebruikt in deze taken") worden berekend uit de taakdefinities, dus een
onderwerp kan nooit claimen bij werk te horen dat er niet naar verwijst.

Twee dingen om rekening mee te houden bij elke wijziging hieronder:

- `FeatureGate` (`FEATURE_WPG`, `FEATURE_PUBLISHING`) verbergt inhoud niet
  alleen, maar haalt hem volledig weg: uit de pagina, de navigatie, de
  backlinks, de zoekindex en de url (404).
- `ManualTest` legt twee invarianten vast: elk hoofdstuk en elke taakgroep houdt
  minstens één ongated item over, wat er ook uit staat. Een test telt bovendien
  hard `toHaveCount(7)` op de hoofdstukken.

## Samenvatting

| # | Bevinding | Oordeel | Voorstel |
|---|---|---|---|
| M1 | 2FA resetten ontbreekt | Terecht | Toevoegen |
| M2 | DPIA-module ontbreekt | Terecht, groot | Tekst uit #141 overzetten; eigen PR |
| M3 | Algoritmen vastleggen ontbreekt | Terecht | Taak toevoegen |
| M4 | verwerking-vastleggen: stappen arbitrair | Terecht | Stappen herordenen |
| M5 | verwerking-publiceren: stap 3 rolvreemd | Terecht, deels | Stap aanpassen, niet pagina verwijderen |
| M6 | versie-indienen verouderd | Al gedaan in #148 | Aanvullen met nieuw gedrag |
| M7 | akkoord-geven: RIVM-screenshot | Terecht | Screenshot verwijderen |
| M8 | publiceren te summier | Terecht | Uitbreiden, niet verwijderen |
| M9 | rollen: markdown kapot | Terecht, bug gevonden | Inspringing repareren |
| S1 | "aanmaken van een versie" onduidelijk | Terecht | Link toevoegen |
| S2 | algoritmes te summier | Terecht | Alinea + Algoritmeregister |
| N1 | Akkoord van Mandaathouders ontbreekt | Deels — staat er wel | Issue |
| N2 | "Met uw rol" is vaag | Terecht, ander fix | Rolnaam invullen, niet hardcoden |
| N3 | WPG apart in menu | Terecht, maar app-wijziging | Issue |
| N4 | filterknop onvindbaar | Terecht | Herformuleren + link |
| N5 | "Gebruikt in deze taken" bij over-openvwr | Terecht | Blok voorwaardelijk |

Twee voorstellen uit de feedback nemen we **niet** over zoals geformuleerd (M5,
M8) en één fix pakken we anders aan (N2). Toelichting hieronder.

---

## Must have

### M1. 2FA resetten voor een ander

**Terecht, en de knop bestaat al.** `OtpDisableAction` staat op de bewerkpagina
van een gebruiker, zowel in het functioneel beheer (`EditUser`) als binnen een
organisatie (`EditOrganisationUser`). Het label is letterlijk **"2FA resetten"**
(`resources/lang/nl/user.php`), er volgt een bevestiging, en de reset wordt
vastgelegd in het auditlogboek (`UserTwoFactorResetEvent`).

De handleiding beschrijft dit nu alleen van de andere kant: bij *De
authenticator instellen* staat "neem contact op met uw Chief Privacy Officer".
Wat die vervolgens moet doen, staat nergens.

**Voorstel** — twee kleine toevoegingen:

1. Een eigen taak `tweefactor-resetten` in de groep "Beheren". **Besloten**:
   een eigen taak, geen stap binnen *Gebruikers en rollen beheren* — het is de
   meestgestelde vraag, en zo is hij ook vindbaar via zoeken en via het
   takenoverzicht.
2. Een naslag-topic `tweefactor-resetten` in het hoofdstuk *Beheer*, waar de
   taak naar linkt. Daar hoort ook wat de gebruiker daarna merkt (opnieuw
   instellen bij de eerstvolgende login) en dat de reset in het auditlogboek
   komt.

Verwijs vanuit *De authenticator instellen* naar dat nieuwe topic, zodat beide
kanten van de vraag aan elkaar geknoopt zitten.

### M2. De DPIA-module ontbreekt

**Terecht, en het is de grootste post in deze lijst.** In de applicatie zitten
twee registers onder een eigen navigatiegroep **DPIA**
(`NavigationGroup::DPIA`), los van de verwerkingsregisters: `DpiaRecordResource`
(de DPIA met 17 paragrafen) en `DpiaPrescanRecordResource` (de pre-scan). Geen
van beide is feature-gated — ze staan bij iedereen in het menu — en er staat
geen woord over in de handleiding.

Dit is geen kleine aanvulling. Wat erin moet: de pre-scan en zijn uitkomst,
persoonsgegevens (§2), risico's (§16), maatregelen (§17) en hun koppeling,
aandachtspunten, de risicomatrix als advies, artikel 36/AP-raadpleging, en het
feit dat een DPIA net als een verwerking wordt vastgesteld maar *niet*
gepubliceerd.

Goed nieuws: het bronmateriaal ligt er al, op twee plekken.
`docs/dpia-reference-nl.md` beschrijft in §8 wat er gebouwd is en waarom,
inclusief de ontwerpbeslissingen die de gebruiker merkt. En **PR #141 (draft)
heeft de handleidingtekst al geschreven** — zie hieronder.

**Besloten**: de handleiding beschrijft de *bediening* van de module en
verwijst voor de methodiek naar het Model DPIA Rijksdienst. Dat model is
gezaghebbend en wordt landelijk onderhouden; de handleiding gaat dan niet uit de
pas lopen zodra het model verandert.

**Voorstel** — een eigen PR:

- een hoofdstuk `Dpia` in de naslag met topics: *Wanneer een DPIA* (pre-scan),
  *De DPIA invullen*, *Risico's en maatregelen*, *Een DPIA laten vaststellen*;
- twee taken in de groep "Vastleggen": *Een pre-scan DPIA doen* en *Een DPIA
  uitvoeren*.

Let op bij het bouwen: `ManualTest` telt de hoofdstukken hard af
(`toHaveCount(7)`); dat getal moet mee.

#### Wat PR #141 al heeft, en wat eraan mankeert

PR #141 (`feature/qa-manual-testscript`, draft) voegt `docs/handleiding/03_dpia.md`
toe: een DPIA-hoofdstuk dat precies de afgesproken scope heeft — bediening, geen
methodiek — en in de juiste toon geschreven is. Ook zitten er twee bruikbare
screenshots in en een QA-testscript met 20+ DPIA-stappen.

Het is dus grotendeels overschrijfwerk, geen schrijfwerk. Twee kanttekeningen:

**1. Verkeerde structuur.** De PR schrijft tegen `docs/handleiding/*.md`, de
markdown-naar-pdf-opzet die PR #143 heeft vervangen door de PHP-handleiding. De
LaTeX-constructies (`\label`, `\ref`, `Figuur \ref{...}`, `Hoofdstuk \ref{...}`)
moeten worden omgezet naar `Topic`-ankerlinks, en de hoofdstuknummering die de PR
verschuift (`03_` → `04_` enzovoort) is niet meer van toepassing. De PR zelf kan
dus niet gemerged worden zoals hij is; de tekst eruit wel.

**2. Vier feitelijke afwijkingen**, nagelopen tegen de code op deze branch:

| Wat #141 zegt | Wat de code zegt |
|---|---|
| Pre-scan stap "Overig" | Heet **"Kinderen en algoritmes"** |
| Pre-scan stap "Koppelen" | Heet **"Verwerkingen en systemen"** |
| Zes pre-scan-stappen | Het zijn er **negen** — ook Uitkomst, Verwerkingen en systemen, Documenten en bijlagen |
| DPIA-paragrafen "Consultatie" en "Review" | Heten **"Consultatie en advies"** en **"Vaststelling en herziening"** |
| Knop "Versie aanmaken" | Is sinds #144/#148 **"Start vaststellen"** (`SubmitForReviewAction`, ook bij de DPIA) |
| Bewaartermijn "werkt precies hetzelfde als bij een verwerking" | Is bij de DPIA een **vrij tekstveld** (`Textarea::make('retention_periods')`), niet de opzoeklijst *Bewaartermijnen* |

Die laatste is de vervelendste: de hint verwijst de lezer naar het topic
*Bewaartermijnen*, dat over de keuzelijst gaat die hier niet bestaat.

Wat wél klopt en overgenomen kan worden: de knop "DPIA starten" verschijnt
alleen bij uitkomst verplicht/aanbevolen en neemt naam, omschrijving en
koppelingen over; de pre-scan wordt altijd bewaard, ook bij uitkomst "niet
nodig"; een pre-scan heeft zelf geen versies of status; een hoog restrisico na
maatregelen maakt AP-consultatie verplicht (`requiresApConsultation()` leidt dat
af uit `measures->withHighResidualRisk()`); de herzieningstermijn van maximaal
drie jaar (staat zo in `help_review_at`); en een DPIA is niet publiceerbaar.

De twee screenshots zijn OpenVWR-gebrand met de huidige kleuren en direct
herbruikbaar; ze moeten alleen naar `public/handleiding/` verhuizen.

### M3. Algoritmen vastleggen ontbreekt

**Terecht.** `AlgorithmRecordResource` staat in de navigatiegroep Registers, en
er is een naslag-topic *Algoritmes* — maar geen taak. In de takenlaag bestaat
het algoritmeregister dus niet, en dat is precies de laag waar mensen beginnen.

**Voorstel**: taak `algoritme-vastleggen` in de groep "Vastleggen", direct na
*Een verwerking vastleggen*. Stappen naar analogie van die taak, linkend naar de
topics `algoritmes` en `labels-toekennen`, plus `versie-indienen` voor het
vaststellen. Rollen gelijk aan die van *Een verwerking vastleggen*.

Dit hangt samen met S2: de taak wordt pas nuttig als het topic *Algoritmes* meer
is dan drie regels.

### M4. verwerking-vastleggen: stappen 2 t/m 4 zijn arbitrair

**Terecht.** De taak heeft nu vier stappen, waarvan "Vul de bewaartermijnen in"
en "Geef de verwerking een label" allebei onderdeel zijn van "Vul de gegevens
in". Ze staan bovendien in een volgorde die het formulier niet volgt. De
Wpg-taak ernaast doet het met drie stappen wél goed.

**Voorstel**: terug naar drie stappen, gelijk aan `wpg-verwerking-vastleggen`:

1. Open het register en maak een verwerking aan;
2. Vul de gegevens in — met bewaartermijnen en labels als onderdeel daarvan,
   `topicIds: ['verwerkingsregisters', 'bewaartermijnen', 'labels-toekennen']`;
3. Dien de conceptversie in → `versie-indienen`.

De topics blijven dus alle drie gelinkt; alleen de stapgrenzen kloppen weer. Het
levert ook winst op: stap 3 verbindt de taak nu aan het goedkeuringsproces, wat
er nu helemaal niet in zit.

### M5. verwerking-publiceren: stappen niet afgestemd op rollen

**De bevinding klopt, het voorstel gaat ons te ver.** De taak heeft
`performers: [CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER]`, maar stap 3 ("Richt de
startpagina in") gaat over "Openbare website", en dat is beheerderswerk. Stap 1
en 2 zijn wél gewoon Privacy Officer-werk en kloppen.

Wij stellen voor de taak **niet** te verwijderen, om drie redenen:

1. Publiceren is een echte gebruikerstaak; zonder taak is de functie alleen nog
   via de naslag te vinden.
2. `verwerking-publiceren` is de enige `FEATURE_PUBLISHING`-gated taak en wordt
   in vier tests als fixture gebruikt (`HandleidingTest` 404-gedrag, menu, en
   `ManualTest`). Verwijderen betekent die dekking opnieuw opbouwen op iets
   anders, of verliezen.
3. Het probleem zit in één stap, niet in de pagina.

**Voorstel**: stap 3 herschrijven zodat hij zegt wie het doet — "De inrichting
van de startpagina onder *Openbare website* doet een beheerder; als Privacy
Officer bepaalt u per verwerking of die openbaar is" — of de stap laten vervallen
en het naar de naslag verplaatsen. Daarnaast een issue voor de bredere vraag of
publiceren en websitebeheer wel in één taak thuishoren (zie M8).

### M6. Naslag: versie-indienen is verouderd

**Op de proeftuin klopt dit; op deze branch is het al gerepareerd.** PR #148 —
en #144 daarvoor — hebben het topic hernoemd van `versie-aanmaken` naar
`versie-indienen` en de tekst herschreven naar de nieuwe knop "Start
vaststellen", inclusief de conceptversie die bij elke opslag wordt bijgewerkt.
Ook `verwerkingsregisters` en het rechtenoverzicht zijn meegegaan.

De reviewer keek naar de uitgerolde proeftuin, die nog op de oude tekst staat.
Deze bevinding lost zichzelf dus op zodra #148 op productie staat.

**Wat er nog wél ontbreekt** — en wat de review niet kón zien, want het is nieuw
in #148: twee dialogen die de gebruiker vanaf nu tegenkomt.

- *"Er loopt al een vaststelling"* — versie X staat op status Y; doorgaan laat
  die versie vervallen en begint opnieuw.
- *"Geen wijzigingen"* — de registratie is niet gewijzigd ten opzichte van de
  vorige versie, dus er is geen nieuwe versie aangemaakt.

Daarnaast is er een expliciete "Status aanpassen"-actie bijgekomen.

**Voorstel**: `versie-indienen` aanvullen met een korte alinea over beide
meldingen, en `versiestatussen` met de statusovergang. **Besloten**: dit gaat in
deze PR mee, want het documenteert het gedrag dat deze PR toevoegt.

### M7. Naslag: akkoord-geven — RIVM-screenshot

**Terecht, geverifieerd.** `06_snapshots_mandaathouders_uitnodigen.png` toont
RIVM-branding, de oude oranje huisstijl en de titel "Verwerkingsregister" in
plaats van OpenVWR. Het onderschrift "Mandaathouders uitnodigen" klopt bovendien
niet met de inhoud: de afbeelding gaat over het versturen van een notificatie,
niet over akkoord geven.

De andere afbeelding in dat topic
(`07_personal-snapshot-approvals_akkoord_geven.png`) is schoon en toont precies
waar het topic over gaat.

**Voorstel**: de RIVM-afbeelding verwijderen uit het topic en het
png-bestand weggooien. De inhoud van het topic gaat er niet op achteruit — de
uitnodigingsstap hoort bij `versie-indienen`, waar hij ook al staat.

Ter geruststelling: dit is de enige achtergebleven RIVM-afbeelding. De rest is
in #142 gerebrand; de gecontroleerde screenshots tonen OpenVWR met de huidige
kleuren.

### M8. Naslag: publiceren is te summier

**Terecht, maar verwijderen maakt het erger.** Het topic is nu vijf regels en
één "Let op". Wat ontbreekt: hoe de openbare website eruitziet, wat er precies
gepubliceerd wordt (alleen vastgestelde versies van openbare verwerkingen), de
verhouding tussen publiek en privé per veld, en wat er gebeurt als je een
verwerking op niet-openbaar zet nadat hij gepubliceerd is.

Verwijderen zou betekenen dat de functie helemaal niet meer beschreven is, én
het haalt het enige `FEATURE_PUBLISHING`-topic weg (zie M5 voor de
testconsequenties).

**Voorstel**: het topic uitbreiden in plaats van verwijderen, en het issue
gebruiken voor wat we nog moeten uitzoeken. Er is materiaal:
`docs/static_website_hugo.md` beschrijft de bouw van de statische website.

**Issue aanmaken**, zoals gevraagd, met de scope van M5 en M8 samen: *"Handleiding:
publiceren en beheer van de openbare website uit elkaar trekken"* — de taak gaat
over de Privacy Officer die een verwerking openbaar maakt, het websitebeheer is
een beheerderstaak en verdient een eigen plek.

### M9. Naslag: rollen — markdown gaat mis

**Terecht, en we hebben de oorzaak gevonden.** Het is geen inhoudelijk probleem
maar een PHP-heredoc-bug in `RollenEnRechten.php`.

Het topic `rollen` plakt twee stukken aan elkaar:

```php
body: <<<'MARKDOWN'
    ...tekst...
    MARKDOWN . self::rolbeschrijvingen(),
```

De heredoc in `rollen()` sluit op 16 spaties inspringing, die in
`rolbeschrijvingen()` op 12 (regel 176) terwijl de inhoud op 16 staat. PHP
stript bij een heredoc precies zoveel inspringing als de *sluitmarkering* heeft.
Gevolg: elke regel van `rolbeschrijvingen()` houdt vier spaties over, en vier
spaties inspringing is in markdown een *codeblok*. Alle rolbeschrijvingen —
Chief Privacy Officer tot en met Functioneel beheerder — worden dus als
monospace codeblok gerenderd in plaats van als tekst met koppen en lijsten.

Nagerekend met een los PHP-script: bij een sluitmarkering die vier spaties
minder inspringt dan de inhoud, begint elke regel met vier spaties.

**Voorstel**: de sluitmarkering op regel 176 uitlijnen met de rest (16 spaties).
Eén wijziging, en het hele blok rendert weer normaal. Overweeg meteen een test
die controleert dat geen enkel topic een onbedoeld `<pre><code>` bevat, want
deze klasse fout is met het blote oog in de PHP-broncode vrijwel onzichtbaar.

---

## Should have

### S1. "het aanmaken van een versie" is een onbekend begrip

**Terecht.** De intro van *Een verwerking vastleggen* verwijst naar een begrip
dat de lezer op dat moment nog niet kent.

Let op: door #148 heet het geen "versie aanmaken" meer. De zin moet dus sowieso
mee, en wordt iets als: "U kunt tussentijds opslaan; pas als u de conceptversie
indient, wordt gecontroleerd of alles is ingevuld."

**Voorstel**: die formulering, met "de conceptversie indient" als link naar de
taak *Een versie indienen en laten goedkeuren*. Dat sluit ook aan op M4, waar
die taak stap 3 wordt.

### S2. Naslag: algoritmes is erg summier

**Terecht.** Het topic zegt alleen dat het register "op identieke wijze werkt"
als de verwerkingsregisters. Waaróm een organisatie algoritmes registreert staat
er niet, en het landelijke Algoritmeregister wordt niet genoemd — terwijl dat
voor overheidsorganisaties de aanleiding is.

**Voorstel**: een alinea over de context (transparantie over algoritmes,
verhouding tot het landelijke Algoritmeregister op
[algoritmes.overheid.nl](https://algoritmes.overheid.nl/)), plus wat OpenVWR
biedt: de publicatiecategorieën, thema's en statussen die als eigen resources in
de applicatie zitten (`AlgorithmPublicationCategoryResource`,
`AlgorithmThemeResource`, `AlgorithmStatusResource`) sluiten aan op de velden
van dat register.

Wel eerlijk blijven: OpenVWR publiceert **niet** automatisch naar het landelijke
register. Dat suggereren zou erger zijn dan het huidige zwijgen.

---

## Nice to have

### N1. "Akkoord van Mandaathouders" ontbreekt

**Deels onterecht.** Er *is* een naslag-topic `akkoord-geven`, en de taak *Een
versie indienen en laten goedkeuren* heeft een stap "Koppel eventueel
Mandaathouders". Wat ontbreekt is een taak vanuit het perspectief van de
Mandaathouder zelf: die logt in, ziet "Mijn Ondertekeningen" en wil weten wat
hij moet doen.

**Voorstel**: conform de feedback nu laten en een issue aanmaken —
*"Handleiding: taak voor de Mandaathouder die akkoord moet geven"*. Punt van
aandacht voor dat issue: de Mandaathouder is in alle bestaande taken `reader`,
nooit `performer`; dit zou de eerste taak zijn die hij zelf uitvoert.

### N2. "Met uw rol kunt u deze taak zelf uitvoeren"

**De bevinding klopt, het voorstel niet helemaal.** Die zin komt uit
`resources/lang/nl/manual.php` en wordt per bezoeker gekozen op basis van
`TaskCapability` — de lezer ziet één van drie varianten, afhankelijk van zijn
eigen rollen. Er hardcoded "Als Privacy Officer..." van maken zou dus voor
Invoerders en Mandaathouders onwaar worden.

**Voorstel**: de rol wél noemen, maar de echte — de vertaalstring een parameter
geven en de rolnamen van de gebruiker invullen: "Als Privacy Officer kunt u deze
taak zelf uitvoeren." Bij meerdere rollen de rol die de taak daadwerkelijk mag
uitvoeren. Dat vraagt een kleine wijziging in `taak.blade.php` plus
`TaskRoles`, en lost de klacht op zonder onwaarheden voor andere rollen.

Voor `role_cannot` staat er al een link naar *Rollen en rechten*; dat is de
plek waar "wat is een rol überhaupt" beantwoord wordt.

### N3. WPG apart in menu en onder Taken

**Terecht als observatie, maar het is deels een applicatiewijziging.** In de
navigatie zitten `WpgProcessingRecordResource` en `AlgorithmRecordResource`
allebei in `NavigationGroup::REGISTERS`, naast de AVG-registers — dus daar staat
WPG al onder Registers. Wat de reviewer ziet is dat het een aparte *ingang* is,
en dat de handleiding een aparte taak en een apart topic heeft.

Samenvoegen raakt meer dan documentatie: `FEATURE_WPG` gate't nu precies één
taak en één topic. Verdwijnen die in de algemene teksten, dan moet de gate
verplaatst worden naar tekstfragmenten binnen een topic — dat kan de huidige
`FeatureGate` niet, die werkt per topic/taak. Bovendien is `wpg-register` de
fixture voor de 404-test op gated topics.

**Voorstel**: nu niet doen, en een issue aanmaken: *"Handleiding: WPG opnemen in
de algemene registertaken in plaats van een aparte taak"*, met de gate-vraag
expliciet benoemd. Als de app zelf het onderscheid ooit laat vallen, volgt de
handleiding vanzelf.

### N4. "Gebruik de filterknop boven een overzicht"

**Terecht.** Staat in twee taken (*Labels gebruiken* stap 4, *Een overzicht
opvragen* stap 1) en zegt niet waar die knop zit. Op de gecontroleerde
screenshot is het een trechter-icoon rechtsboven de tabel, naast het zoekveld.

**Voorstel**: beide stappen herformuleren naar "Gebruik de filterknop — het
trechter-icoon rechtsboven de tabel — om ..." en in beide gevallen `topicIds`
op `filteren-op-labels` houden, zodat de link naar de uitleg blijft staan. Beide
stappen linken daar al naartoe; de tekst maakt dat nu alleen niet zichtbaar.

### N5. "Gebruikt in deze taken" bij over-openvwr

**Terecht.** `over-openvwr` heeft geen enkele backlink, dus het blok toont de
tekst "Dit onderwerp is naslag zonder bijbehorende taak: u zoekt het op terwijl
u met iets anders bezig bent." Bij een *Over*-pagina is dat inderdaad zinloos.

Het blok zelf is wel waardevol op topics die er wél bij horen, en het is bewust
berekend uit de taakdefinities.

**Voorstel**: het blok niet tonen als er geen backlinks zijn — in
`onderwerp.blade.php` de hele `<section>` achter de `@if ($usedIn !== [])`
zetten in plaats van alleen de lijst. Dan verdwijnt ook `used_in_no_tasks` uit
de vertalingen. Dat is netter dan een uitzondering voor één topic, en het helpt
elk toekomstig los naslagonderwerp.

---

## Stand van zaken

Uitgevoerd op deze branch: M1, M3, M4, M5, M6, M7, M8, M9, S1, S2, N2, N4, N5.

Nog open: M2 (DPIA, eigen PR) en de drie issues (N1, N3, en publiceren/websitebeheer).

Twee dingen liepen anders dan het plan hierboven beschreef:

- **S1** vroeg om een markdown-link in de intro van een taak. Dat kan niet:
  `taak.blade.php` rendert `intro`, `Step::body` en `done` met `{{ }}`, dus als
  geëscapete platte tekst. Een markdown-link zou letterlijk als `[tekst](url)`
  op het scherm komen. In de takenlaag is `topicIds` het linkmechanisme; de term
  is daarom herschreven en de link loopt via de nieuwe derde stap.
- **M5** bleek scherper dan gedacht: `PublicWebsiteTreeResource` staat in
  `NavigationGroup::FUNCTIONAL_MANAGEMENT`. De stap "Richt de startpagina in"
  was dus niet alleen rolvreemd voor een Privacy Officer, hij wees naar een
  onderdeel dat die rol niet eens ziet.

Nog op te pakken, gevonden tijdens het werk: de rolnamen in
`resources/lang/nl/role.php` schrijven "Chief privacy officer" met kleine
letters, terwijl de handleiding overal "Chief Privacy Officer" schrijft. Door N2
staat die schrijfwijze nu ook boven elke taak. Die strings worden gedeeld met de
rol-toggles en -tabellen in de rest van de applicatie, dus dit rechttrekken is
een UI-wijziging buiten het bestek van deze documentatie-PR.

## Voorgestelde volgorde

**Deze PR** (klein, laag risico, geen overleg nodig): M9 (heredoc-bug), M7
(RIVM-screenshot), M4 (stappen), S1 (link), N4 (filterknop), N5 (backlink-blok).

**Direct erna** (nieuwe inhoud, wel schrijfwerk): M1 (2FA resetten), M3 + S2
(algoritmes), M5 (publiceren-stap), M8 (publiceren uitbreiden), M6 (nieuw gedrag
uit #148).

**Eigen PR**: M2 (DPIA) — de tekst uit #141 overzetten naar de PHP-structuur en
de zes afwijkingen repareren. Stem af met de eigenaar van #141 wat er met die
draft gebeurt: de markdown-kant is achterhaald, maar het QA-testscript
(`docs/qa/qa-manual-testscript.md`) staat daar los van en is op zichzelf
bruikbaar.

**Issues aanmaken**: publiceren/websitebeheer (M5+M8), Mandaathouder-taak (N1),
WPG samenvoegen (N3).

**Apart en wenselijk**: N2 vraagt een kleine codewijziging in de rendering, geen
tekstwijziging.

## Besloten

1. **DPIA-diepgang** (M2): de bediening van de module; voor de methodiek
   verwijzen naar het Model DPIA Rijksdienst.
2. **Nieuw gedrag uit #148** (M6): gaat in deze PR mee.
3. **2FA resetten** (M1): een eigen taak, geen stap binnen *Gebruikers en rollen
   beheren*.
