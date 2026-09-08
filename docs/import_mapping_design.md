<!--
SPDX-FileCopyrightText: 2026 Jeroen
SPDX-License-Identifier: EUPL-1.2
-->

# Ontwerp: generieke mapping-laag voor import

Status: **stap 1–8 geïmplementeerd** (zie §6). Stap 9 nog open.

Na de code- en securityreview van september 2026 is de implementatie
aangescherpt; zie §8 voor wat daar uit voortkwam en waarom.

| Onderdeel | Klasse |
|---|---|
| Sheet lezen (xlsx/csv, dot-notatie) | `App\Import\Mapping\SheetReader` |
| Ongewijzigde ZIP-route | `App\Import\Importers\SpreadsheetImporter` |
| Declaratief profiel | `App\Import\Mapping\MappingProfile` + `MappingField` |
| Uitvoering | `App\Import\Mapping\MappingEngine` |
| Heuristiek (NL-labels) | `App\Import\Mapping\MappingAnalyser` |
| Proefdraai, lijst A/B | `App\Import\Mapping\DryRunner` |
| Profielen bewaren | `App\Import\Mapping\MappingProfileRepository`, `App\Models\ImportMappingProfile` |
| Wizard-UI | `App\Filament\Pages\ImportMapping` (state), `App\Import\Mapping\EditableMapping` + `ColumnReview` (reviewscherm), `TargetOptions` (aangeboden doelen) |
| Wegschrijven | `App\Import\Mapping\MappedRecordWriter` (per rij een transactie, idempotent op `import_id`) |
| Gedeelde entiteiten | `App\Import\Mapping\EntityResolver` |
| Datalek-factory | `App\Import\Factories\DataBreachRecordFactory` |

Doel: elke platte bron (Excel/CSV) kunnen migreren naar elk register in OpenVWR,
zonder per bron een PHP-factory te schrijven.

## 0. Scope: snapshots zijn uitgesloten

De Excel-route maakt **geen snapshots** en zet **geen workflow-status**. Een
geïmporteerd record komt als los bronrecord binnen en staat daarmee nog volledig
buiten de vaststellings-/reviewworkflow; die doorloopt een mens daarna in de UI.

Gevolgen door het hele ontwerp:

- `SnapshotHelper` en `StateHelper` worden door de mapping-laag **niet** gebruikt;
- het profiel (§4.2) kent geen `status`/`versie`-velden en mag ze niet aanbieden;
- `import.states_to_skip_import` en `import.value_converters.snapshot_state` uit
  `config/import.php` gelden alleen voor de JSON-route.

**De bestaande JSON-import blijft ongewijzigd.** `AvgResponsibleProcessingRecord`,
`AvgProcessorProcessingRecord` en `WpgProcessingRecord` blijven via hun huidige
factories snapshots maken; daar verandert niets aan. De uitsluiting geldt
uitsluitend voor de nieuwe Excel-/mappingroute.

## 1. Probleemstelling

De bestaande import (`App\Import`) werkt goed, maar de mapping zit **hardcoded in
PHP**. Per bronformaat een nieuwe factory schrijven schaalt niet.

Excel-bronnen slaan plat wat in OpenVWR gestructureerd is. Vier verschillende
problemen, die vaak op één hoop worden gegooid:

| # | Probleem | Voorbeeld |
|---|---|---|
| P1 | Kolomnaam ≠ veldnaam | `Naam verwerking` → `name` |
| P2 | Nesting verdwijnt | `Beveiliging.Encryptie` is één cel |
| P3 | 1-op-veel verdwijnt | 3 verwerkers in één cel of 3 rijen |
| P4 | Entiteit wordt string | `Leverancier: "Firma A"` → `Processor`-record + relatie |

P4 is de lastigste en de belangrijkste: dezelfde string moet **hetzelfde record**
opleveren, anders krijg je 40 losse "Firma A"-records.
Zie `app/Console/Commands/SystemUndouble.php` — dat commando bestaat omdat dit
in productie al is misgegaan.

## 2. Wat er al is (niet opnieuw bouwen)

| Bestaand | Doet | Locatie |
|---|---|---|
| `ZipImporter` | ZIP uitpakken, extensie → importer, bestandsnaam → factory | `app/Import/ZipImporter.php` |
| `JsonImporter` | JSON → per rij een job | `app/Import/Importers/JsonImporter.php` |
| `ImportEntityJob` | queued, encrypted, per rij | `app/Jobs/ImportEntityJob.php` |
| `Factory` interface | `create(array $data, UuidInterface $orgId)` | `app/Import/Factory.php` |
| `DataConverters` | `toString/toBoolean/toCarbon/toArray` met **dot-notatie** | `app/Import/Factories/Concerns/` |
| `RelationHelper` | `createRelations()` voor MorphMany/MorphToMany | idem |
| `SnapshotHelper` | snapshot + workflow-status | idem — **niet gebruikt door mapping-laag** (§0) |
| `LookupListFactory` | **match op naam**, hergebruik indien bestaand | `app/Import/Factories/General/` |
| `openspout/openspout` v4 | XLSX/CSV/ODS **reader** (al in vendor) | via Filament |

Twee bestaande dedup-patronen:
- `firstOrNew(['import_id' => ...])` — match op bron-ID (JSON-migraties)
- `LookupListFactory` — match op **naam** (dit is de kiem voor P4)

## 3. Waarom niet Filament's ImportAction

Filament v3.3.45 `ImportAction` heeft een kolom-mapping-GUI en `->relationship()`.
Voor een plat model prima. Als fundament voor "alles in OpenVWR" niet:

1. **Eén rij = één model.** `Importer::$model` is één klasse. De registers doen 8
   `createRelations()`-aanroepen naar aparte factories.
2. **`resolveRelatedRecord()` is BelongsTo-only** (letterlijk zo geannoteerd in
   `vendor/filament/actions/src/Imports/ImportColumn.php`). De gedeelde entiteiten
   zijn `MorphToMany`.

(Een derde bezwaar — dat ImportAction geen snapshots kan maken — vervalt door de
scope-afbakening in §0.)

Wel bruikbaar als *inspiratie*: `guess()`, `castStateUsing()`, `->array(separator:)`
en de gecachete relatie-resolutie zijn goede ideeën die hieronder terugkomen.

## 4. Ontwerp

Kernprincipe, ontleend aan FHIR StructureMap / JSONata / dbt:
**scheid de mapping-declaratie van de mapping-uitvoering.**
De declaratie is data (DB/YAML), niet PHP. Daarmee wordt een GUI later een schil,
geen herschrijving.

```
Excel  →  SpreadsheetImporter  →  rijen
              ↓  (headers met dot-notatie)
          Arr::undot()          →  geneste array   [lost P1, P2 op]
              ↓
          MappingProfile        →  genormaliseerd record
              ↓  (per veld: pad + transform)
          EntityResolver        →  gedeelde entiteiten [lost P4 op]
              ↓
          bestaande Factory     →  model + relaties  (géén snapshot, §0)
```

De bestaande pipeline blijft dus intact; er komt een laag vóór.

### 4.1 SpreadsheetImporter (P1, P2)

Implementeert de bestaande `Importer`-interface, dus `ZipImporter` en
`ImportEntityJob` hoeven niet te veranderen. Registratie in `config/import.php`:

```php
'importers' => [
    'json' => JsonImporter::class,
    'xlsx' => SpreadsheetImporter::class,
    'csv'  => SpreadsheetImporter::class,
],
```

Kern: headerrij → array-keys, dan `Arr::undot()`. Omdat `DataConverters` al
`Arr::get()` met dot-notatie gebruikt, werken **alle bestaande factories dan
meteen ook vanuit Excel**:

| Excel-kolomkop | wordt | bestaande factory-code |
|---|---|---|
| `Beveiliging.Encryptie` | `$data['Beveiliging']['Encryptie']` | `toString($data, 'Beveiliging.Encryptie')` |
| `Verwerkers.0.Naam` | `$data['Verwerkers'][0]['Naam']` | `createRelations(..., 'Verwerkers', ...)` |

Let op:
- lege rijen en lege trailing kolommen filteren (`Assert::isMap` faalt anders);
- XLSX-datumcellen komen als `DateTimeImmutable`, niet als string — `toCarbon()`
  verwacht nu `Assert::string()`. Dit is de plek waar het stil misgaat.

### 4.2 MappingProfile (P1, P3)

Het profiel is het **declaratieve model in het midden**: het resultaat van
heuristische analyse, bewerkbaar door de gebruiker, en pas daarna uitgevoerd.
Het is een artefact van de sessie — geen vooraf geschreven configuratie.

Vorm (JSON-serialiseerbaar, opgeslagen bij de import-sessie):

```yaml
name: "Zenya VIM-export"
target: DataBreachRecord
identity: "Meldnummer"          # idempotentie → import_id
fields:
  - source: "Datum melding"     # kolomkop (of dot-pad)
    target: reported_at
    transform: date
    confidence: exact           # exact | label | fuzzy | none  (zie 4.5)
  - source: "Omschrijving"
    target: summary
    confidence: fuzzy
  - source: "Leverancier"
    target: processors           # relatie, geen kolom
    resolve: Processor           # → EntityResolver
    split: "\n"                  # één cel → meerdere entiteiten [P3]
unmapped:                        # expliciet: bewust niet geïmporteerd
  - "Melder"                     # anonimiteit, zie §7
```

`transform` is een **kleine, gesloten set** — bewust geen expressietaal:
`string`, `date`, `boolean`, `integer`, `array`, `implode`, `lookup`.
Dit dekt alles wat de huidige factories doen (`DataConverters` heeft precies deze
conversies). Een eigen expressietaal is de val waar FHIR StructureMap in trapt.

`split` lost de "één cel, meerdere waarden"-variant van P3 op.
De "meerdere rijen per entiteit"-variant (groeperen op sleutelkolom) is
**expliciet buiten scope** van v1 — dat breekt het "één rij = één job"-model en
moet pas als een echte bron het vereist.

`unmapped` is niet cosmetisch: het dwingt af dat "deze kolom negeren" een
**bewuste keuze** is die zichtbaar blijft, in plaats van een kolom die stil
wegvalt. Bij VIM-bronnen is dat het verschil tussen wel en niet lekken van
meldergegevens.

### 4.5 Sessieverloop: analyse → voorstel → dry-run → apply

Het profiel wordt niet geschreven maar *voorgesteld*. Verloop:

```
1. upload      gebruiker kiest Excel + doelregister
2. analyse     heuristiek leest headers + steekproef van rijen
                 → MappingProfile met confidence per veld
3. review      gebruiker ziet het model, past aan, kolommen naar `unmapped`
4. dry-run     volledige mapping, niets opgeslagen
                 → lijst A: past    (n records)
                 → lijst B: twijfel (n records + reden)
5. herstel     gebruiker re-mapt op basis van lijst B  ─┐
                 → terug naar 3                          │ repeat
6. apply       pas nu echte import (bestaande pipeline) ─┘
```

Stap 4→5→3 is de lus die je wilt: **twijfelgevallen sturen de volgende
mappingronde**, in plaats van dat de gebruiker vooraf alles goed moet raden.

**Heuristiek (stap 2).** Filament's `guess()` is puur lexicaal (lowercase,
`-`/`_` variaties) en helpt niet van Nederlandse kolomkop naar Engelse veldnaam.
OpenVWR heeft echter iets beters liggen: `resources/lang/nl/*.php` bevat per
model een complete Nederlandse veldwoordenlijst.

```php
// resources/lang/nl/data_breach_record.php
'reported_at'    => 'Datum melding',
'discovered_at'  => 'Datum ontdekking datalek',
'summary'        => 'Samenvatting incident',
```

Een Zenya-kolomkop "Datum melding" matcht daar **letterlijk** op. Dit is de
belangrijkste heuristiek en hij is gratis. Volgorde:

| # | Signaal | Confidence |
|---|---|---|
| 1 | exacte match op veldnaam of dot-pad | `exact` |
| 2 | exacte match op NL-label uit `resources/lang/nl/` | `exact` |
| 3 | genormaliseerde match op NL-label (case, spaties, leestekens) | `label` |
| 4 | **inhoudsanalyse steekproef**: kolom bevat alleen datums → datumveld; ja/nee → boolean; waarden ⊂ `type_options` → enum | `fuzzy` |
| 5 | geen match | `none` → default `unmapped` |

Signaal 4 is waardevoller dan naam-gelijkenis: de `lang`-bestanden bevatten ook
`type_options` (bijv. `['Voorlopig', 'Definitief']`), dus een kolom waarvan alle
waarden in die lijst zitten is vrijwel zeker dát veld — ongeacht de kolomkop.

Bewust **niet**: Levenshtein/fuzzy string matching op kolomnamen. Levert valse
voorstellen op die de gebruiker moet ontkrachten; een leeg voorstel is beter dan
een fout voorstel.

**Dry-run (stap 4).** Draait de volledige mapping inclusief `EntityResolver`,
maar in een transactie die terugdraait — of tegen een in-memory model. Levert
twee lijsten:

| Lijst | Betekenis | Actie gebruiker |
|---|---|---|
| A — past | alle verplichte velden gevuld, transforms geslaagd | — |
| B — twijfel | transform faalde, verplicht veld leeg, of `EntityResolver` vond een *bijna*-match | re-mappen of per record beslissen |

Reden per record meesturen ("kolom `Datum melding` bevat `n.v.t.`, geen datum"),
anders kan de gebruiker niet gericht re-mappen.

**Partiële apply.** Lijst A importeren en lijst B laten staan, zodat een paar
rotte records de hele import niet blokkeren. Dit vereist dat `apply` idempotent
is (zie `import_id`), anders levert een tweede ronde duplicaten.

### 4.6 Profielen opslaan en hergebruiken

Een goedgekeurd profiel is het waardevolste product van de sessie: er zit
menselijk oordeel in dat geen heuristiek reproduceert. Zonder opslag begint elke
maandelijkse Zenya-export weer bij stap 2, en beslist een andere medewerker
mogelijk anders — dan is de import niet reproduceerbaar, wat voor een wettelijk
register een probleem is.

Model: `ImportMappingProfile`

| Veld | Doel |
|---|---|
| `name` | "Zenya VIM-export maandelijks" |
| `organisation_id` | tenant-scoped, net als alle andere modellen |
| `target_model` | `DataBreachRecord` |
| `mapping` | het profiel uit 4.2 (JSON-kolom) |
| `source_fingerprint` | genormaliseerde set kolomkoppen (zie onder) |
| `version` | profielen zijn immutable; bewerken = nieuwe versie |
| `created_by` / `created_at` | wie heeft deze mapping goedgekeurd |

**Herkenning bij upload.** Bij stap 2 wordt eerst gezocht naar een bestaand
profiel voordat de heuristiek draait:

```
fingerprint = sha256(gesorteerde, genormaliseerde kolomkoppen)

exacte match      → profiel voorstellen, direct door naar dry-run (stap 4)
gedeeltelijke match → profiel voorstellen + diff tonen:
                       "3 nieuwe kolommen, 1 verdwenen" → stap 3
geen match          → heuristiek (stap 2)
```

De gedeeltelijke match is het praktijkgeval: Zenya-exports veranderen als iemand
het publieke filter aanpast (zie de bekende kolominstellingen-valkuil). Dan wil
je niet opnieuw beginnen, maar alleen het verschil beoordelen.

**Immutable versies.** Een profiel bewerken maakt een nieuwe versie in plaats van
de oude te overschrijven. Reden: een import verwijst naar de profielversie
waarmee hij is uitgevoerd. Zonder dat kun je achteraf niet verantwoorden hoe een
record in het register terecht is gekomen — precies wat een FG bij een audit
vraagt. Kosten zijn verwaarloosbaar (een JSON-kolom per versie).

**Wat NIET meegaat in een profiel:** de `EntityResolver`-beslissingen uit 4.4
("Firma A B.V. = Firma A"). Die horen bij de *entiteiten*, niet bij de mapping —
anders is een profiel niet herbruikbaar tussen organisaties. Sla ze op als
aliassen bij het doelrecord.

### 4.3 EntityResolver (P4) — het belangrijkste onderdeel

Generaliseert `LookupListFactory` van lookup-lijsten naar échte entiteiten
(`Processor`, `System`, `Receiver`, `Responsible`, `ContactPerson`, `Stakeholder`).

```
resolve(modelClass, organisationId, "Firma A") → Model
  1. exact match op naam binnen organisatie      → hergebruik
  2. genormaliseerde match (trim, case, B.V./BV) → hergebruik + rapporteer
  3. geen match                                  → aanmaken + markeer "nieuw"
```

Twee eisen:
- **Cache per import-run.** Filament doet dit met `$resolvedRelatedRecords`;
  zonder cache krijg je binnen één bestand al duplicaten.
- **Rapporteer, beslis niet.** Fuzzy matches worden *voorgesteld*, niet stil
  toegepast. Zie 4.4.

Normalisatie-regels (organisatie-onafhankelijk, conservatief):
trim, dubbele spaties, case-insensitief, rechtsvormen (`B.V.`/`BV`/`bv`),
leestekens aan het eind. **Niet**: Levenshtein/soundex — dat levert valse
positieven op bij leveranciersnamen en is niet uit te leggen aan een FG.

### 4.4 Review-stap (waar de GUI wél waarde heeft)

Niet als kolom-mapper (dat kan de bronbeheerder één keer in de header zetten),
maar als **beslismoment na resolve**:

```
Import vond 12 nieuwe leveranciers. 3 lijken op bestaande:
  "Firma A B.V."  ≈  "Firma A"     [samenvoegen] [apart houden]
```

Dit haalt de beslissing die `SystemUndouble` nu achteraf en handmatig maakt naar
voren, waar hij hoort. Dit is domeinkennis — een mens moet het doen.

## 5. Ontbrekende factories

Onafhankelijk van bovenstaande zijn er twee gaten in de dekking:

| Register | Factory | Bijzonderheid |
|---|---|---|
| `AvgResponsibleProcessingRecord` | ✅ | |
| `AvgProcessorProcessingRecord` | ✅ | |
| `WpgProcessingRecord` | ✅ | |
| `AlgorithmRecord` | ❌ ontbreekt | relaties; snapshots buiten scope (§0) |
| `DataBreachRecord` | ❌ ontbreekt | plat, **geen** snapshots, geen state |

`DataBreachRecord` mist ook een `import_id`-kolom + unique index (idempotentie
én audit-trail terug naar de bron). Kleine migratie.

## 6. Volgorde

Elke stap levert werkende import op; de mapping-abstractie groeit uit twee echte
gevallen in plaats van uit gefantaseerde.

1. **`DataBreachRecordFactory` + `import_id`-migratie** — plat, geen snapshots.
   Levert de Zenya-case op en is de uitvoerder waar de rest op mikt.
2. **`SpreadsheetImporter`** met `Arr::undot()`. Losstaand nuttig: alle bestaande
   factories werken daarna ook vanuit Excel.
3. **`MappingProfile` + `MappingEngine`** — het declaratieve model uit 4.2, nog
   zonder GUI: profiel als YAML/JSON-bestand, uitgevoerd door de engine.
   Hier wordt "elke Excel → elk register" waar.
4. **Dry-run + lijst A/B** (4.5, stap 4). Nog steeds zonder GUI bruikbaar als
   artisan-commando — levert direct waarde bij het testen van (3).
5. **Heuristiek** (4.5, stap 2) op basis van `resources/lang/nl/`.
6. **Profielopslag + fingerprint-herkenning** (4.6).
7. **GUI**: review-scherm (stap 3), lijst A/B (stap 4), profielbeheer.
8. **`EntityResolver`** (4.3) + review-stap (4.4). Geïmplementeerd: exacte
   match, genormaliseerde match (gerapporteerd), dubbelen gemeld, en per
   register een beleid voor onbekende namen (`MissingEntityPolicy`).
9. **`AlgorithmRecordFactory`** — het andere gat; relaties, geen snapshots (§0).
   **Nog open.**

Stap 3 is het architectonische kantelpunt: daarna is een nieuwe bron een profiel
in plaats van PHP. Stap 1–2 zijn geen wegwerpwerk — het zijn de onderdelen
waaruit 3 bestaat. Stap 4–7 maken het bruikbaar voor een privacy officer;
elk van die stappen is los te releasen.

De GUI staat bewust ná de engine: een verkeerd ontworpen GUI op een goede engine
is een middag herbouwen, andersom niet.

## 7. Open vragen

- Kent de bron een aparte categorie/filter voor de gewenste subset (bij Zenya:
  privacy/datalek-meldingen)? Bepaalt of filteren aan de bronkant kan.
- Welke flatten-vorm gebruikt de bron voor 1-op-veel (P3): herhaalde kolommen,
  één cel met scheidingsteken, of meerdere rijen? De derde vorm vergroot stap 2.
- Anonimiteit: bij VIM-bronnen mogen meldergegevens meestal **niet** mee. Dit
  moet expliciet in het profiel, niet per ongeluk.

## 8. Aanscherpingen na review (september 2026)

Wat de review vond en hoe het is opgelost. De volgorde is die van ernst.

| Bevinding | Oplossing |
|---|---|
| Upload werd geparsed vóór validatie (virusscan, bestandstype) door de `live()`-hook | `analyse()` haalt eerst `getState()` op; pas daarna wordt het bestand geopend |
| Profielen werden organisatie-overstijgend gezocht (fingerprint, versienummer) | `MappingProfileRepository` neemt overal expliciet een `organisationId` |
| Datums gingen als tekst naar Eloquent en werden door Carbon Amerikaans geraden | Het datumformaat is onderdeel van de mapping, per kolom: `DateFormatDetector` bepaalt welke formaten uit `import.mapping.date_formats` op álle voorbeeldwaarden passen; één → vastgelegd, meerdere → de gebruiker kiest (in datums, niet in formaatstrings); de engine leest de kolom strikt in dat formaat, en het formaat gaat mee in het profiel |
| CSV met puntkomma (Nederlands Excel) werd één kolom | `SheetReader` bepaalt het scheidingsteken uit de kopregel |
| Dubbele of overlappende kolomnamen overschreven elkaar stil | Geweigerd met een melding |
| `apply()` was niet idempotent en slikte fouten in, zonder transactie | `MappedRecordWriter`: per rij een transactie, rijen met bekend `import_id` overgeslagen, mislukte rijen per nummer gemeld |
| Foutlog bevatte de SQL met celwaarden (persoonsgegevens) | Alleen exceptieklasse, code en rijnummer worden gelogd |
| `state` en foreign keys stonden als doel in de lijst; doelen werden niet server-side gecontroleerd | `TargetOptions` bepaalt en valideert de lijst; een onbekend doel geeft 403 |
| Alle rijen reisden in de Livewire-payload mee bij elke wijziging | Rijen staan in de cache onder een per-gebruiker sleutel; publieke state is `#[Locked]`; maximaal `import.mapping.max_rows` |
| `EntityResolver` scande per naam de hele tabel | Eén genormaliseerde index per model per run |
| Zip-inspectie kende geen limieten | Dezelfde limieten als `ZipImporter` |
| Pagina van 1.200 regels, complexiteit 148 | Gesplitst in `TargetOptions`, `EditableMapping`/`ColumnReview` en `MappedRecordWriter` |

Uit een proefimport van een echt verwerkingsregister-sjabloon (september 2026):

| Bevinding | Oplossing |
|---|---|
| Kopcellen bevatten instructies ("Omschrijving\nNoteer hier…", "Verwerkers: Noteer hier…", "…, meerdere keuzes mogelijk.") | `SheetReader` houdt alleen de naam over: eerste regel, tekst vóór een dubbele punt met zin erachter, zonder invulinstructie of toelichting tussen haakjes |
| De analyser stelde velden voor die het scherm niet aanbiedt (`public_from`) en kende koppelingen en opzoeklijsten niet | De kandidaten zijn precies de opties van `TargetOptions`; "Verwerkers", "Contactpersoon", "Verwerkingsdoel" worden nu herkend |
| Doelen, grondslag, betrokkenen en contactpersonen hadden geen doel | `contactPersons`, `stakeholders`, `avgGoals` (met grondslag als extra kolom) en `wpgGoals` zijn gedeelde entiteiten |
| Ja/nee-waarden lieten een losse naamgelijkenis ("Verwerkers overeenkomst?" → "Heeft verwerkers") als zeker doorgaan; "Omschrijving" werd op string-gelijkenis aan "Toelichting doorgifte" gekoppeld | Inhoud bevestigt een naam maar maakt hem niet zeker; een kop die maar een fragment van een label is blijft een voorstel |
| "J"/"N" werden niet als ja/nee gelezen | Toegevoegd aan de engine |

Uit een proefimport van een datalek-sjabloon (september 2026):

| Bevinding | Oplossing |
|---|---|
| Koppen met een waardelegenda ("Gemeld aan betrokkenen        Ja=1") | `SheetReader` laat de legenda en de opvulling weg |
| Een kolom met AP-nummers werd op naam ("Gemeld aan AP") als ja/nee-veld voorgesteld; een kolom met vrije tekst als "Type" (Voorlopig/Definitief) | Waarden die nergens in passen tellen tegen de naam, ook bij een exacte naam. Vaste keuzelijsten (`<veld>_options` in `resources/lang`) gelden als bewijs: erin → het veld, erbuiten → niet (`FieldOptions`) |
| Verplichte velden zonder bronkolom (`fg_reported`, `type`) blokkeerden elke rij | `FormDefaults`: een verplicht ja/nee-veld begint als "nee", een verplichte vaste keuze als de eerste optie, precies zoals het formulier; alleen kolommen die de database afdwingt |
| Losse regels onder een record (extra keuzewaarden in één cel) | Komen als eigen rij binnen en vallen in de proefdraai af op de ontbrekende naam; de gebruiker ziet ze als aandachtsrij |

Uit het QA-rapport van een teamlid (september 2026), met een export van een
ander registerpakket van 93 kolommen:

| Bevinding | Oplossing |
|---|---|
| Kolomnamen als "RasOfEtniciteit", "IsBronBetrokkene" en "Omschrijving23" werden niet herkend | `FieldSynonyms` knipt camelCase en cijfers los en laat losse volgnummers weg |
| Hetzelfde doelveld was voor twee kolommen te kiezen; de laatste won stil | `EditableMapping::duplicateTargets()`; proefdraaien en importeren weigeren met beide kolomnamen. Koppelingen en notities nemen wél meerdere kolommen |
| Een kolom "Tekst" met verzamelinformatie had geen bestemming | Doel "Notitie" (`RelationKey::REMARKS`): per kolom een `Remark` "Kolomnaam: waarde", alleen voor registers met opmerkingen |
| Proefdraai zonder aandachtsrijen, import "0 geïmporteerd, 0 overgeslagen" | De kop van het resultaat telt nu de mislukte rijen; de reden is per SQLSTATE vertaald (`WriteFailureReason`) zonder celwaarden; waarden langer dan de kolom (`FormDefaults::lengths()`) vallen al in de proefdraai af |
| De analyser gaf een kolom het vierde beste doel als de betere al bezet waren ("Verantwoordelijke rechtspersoon" → Datalekken) | Alleen kandidaten dicht bij de beste score van de kolom blijven over |
| Een lege cel in een gekoppelde kolom zette het veld expliciet op NULL, langs het formulierdefault heen | De writer laat lege waarden weg; het veld begint als op het formulier |

De rondreis met de eigen Excel-export (`ImportRoundTripTest`: exporteren met
de echte `Exporter`, inlezen via `SheetReader` en `MappingAnalyser`, importeren
via de pagina) legde bloot wat de export en de import van elkaar afweken:

| Bevinding | Oplossing |
|---|---|
| Zeven kolommen "Namelijk" in de datalek-export; `SheetReader` weigert dubbele koppen | De export prefixt ze met het bovenliggende veld ("Aard van incident — Namelijk"), zoals de keuzelijst van de import al deed |
| `reported_to_involved` ontbrak in de datalek-export; de kop van het nummer toonde een ruwe vertaalsleutel | Toegevoegd, sleutel hersteld |
| De verwerkingsregisters exporteerden beveiligingsvelden onder de labels van de verwerker ("Toelichting maatregelen") in plaats van die van het formulier | Export gebruikt de eigen labels |
| Export schrijft ja/nee als `yes`/`no`, datums als `04-03-2026 00:00` en lijsten als "Naam, Adres" | Scorer en `ColumnReview` kennen yes/no; datum met tijd geldt als datum; `MultiValue` splitst een cel zonder regeleinden op komma-spatie (lijsten, koppelingen, keuzelijst-bewijs) |
| De exports lieten velden en koppelingen weg (verwerkersdetails, contactpersonen, grondslag, gekoppelde registers), de WPG-export had dubbele kolommen (artikel 19 en 23 tweemaal, "Politie/Justitie" op het verkeerde veld, driemaal "Toelichting") en de labels weken af van het formulier | `Exporter::relatedListColumn()` schrijft attributen van een lijst met één invoer per record, lege plekken inbegrepen; `ExportImportParityTest` eist dat elk importdoel als kolom in de export staat en dat geen kop dubbel is; labels komen uit dezelfde taalsleutels als de import; `service` en `role` zijn fillable zonder kolom en worden niet meer aangeboden |
| `data_collection_source` (enum) werd als tekstveld aangeboden; de export schrijft het label, de cast weigert dat | `EnumField`: de labels van de enum zijn de keuzes (bewijs voor de analyser, default voor `FormDefaults`), de proefdraai weigert een waarde die geen keuze is, de writer zet het label om in de case |
| "Labels" en "Periodieke review" uit de export hadden geen doel | `tags` is een gedeelde entiteit van elk register; `review_at` is een gewoon datumveld en geen intern veld meer |

Uit het bestand van het QA-teamlid (RIVM-export, 93 kolommen, 9 rijen die
samen één verwerking zijn: de kolommen van het record herhaald op elke rij,
per rij één item van één lijst):

| Bevinding | Oplossing |
|---|---|
| Iedere rij werd een record; met het Id als bronkenmerk werd rij 1 geïmporteerd en de rest overgeslagen, zodat systemen, doelen en betrokkenen nooit aankwamen | `RecordGrouping` stelt de kolom voor waarvan de herhaalde waarde de rijen van één record markeert (alle kolommen die op elk van die rijen gevuld zijn stemmen overeen, alleen de ijle kolommen verschillen); de gebruiker bevestigt of kiest een andere kolom. `RecordGrouper` vouwt de rijen samen: een gewoon veld en een opzoeklijst krijgen één waarde (verschil = aandachtsrij), koppelingen, hun attributen en notities krijgen één invoer per rij, lege plekken inbegrepen, zodat de derde naam bij het derde e-mailadres blijft. De kolom gaat als `identity` mee in het profiel |
| Kolomgroepen (Id2, Naam3, Type, Telefoon … beschrijven één verantwoordelijke) | Nog niet: de analyser kent geen blokken en koppelt "Postcode" aan het enige adres dat hij kent (verwerkers) |

Nog niet ondersteund, bewust: kolomgroepen (zie boven), categorieën persoonsgegevens en bewaartermijn
(die horen bij de gegevens per betrokkene, twee niveaus diep), de bijzondere
gegevens per betrokkene (die liggen in OpenVWR op de gedeelde betrokkene, niet
op de verwerking) en een status uit het bronbestand die direct een vastgestelde
versie zou moeten opleveren (dat passeert het goedkeuringsproces). Een naam met
", " erin wordt bij een cel zonder regeleinden in tweeën geknipt; dat is
zichtbaar onder "Nieuw aangemaakt".

Bewust niet gedaan: de phpstan-regel `TenantAwareQueryRule` uitbreiden naar
`App\Import`. De importlaag draait ook in queue-jobs zonder ingelogde tenant
en werkt daarom met een expliciete `organisationId` in plaats van
`tenantQuery()`; de regel zou daar alleen valse meldingen geven.
