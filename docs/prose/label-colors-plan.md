# Plan: kleuren voor labels

Status: voorstel ter bespreking. Nog niets geïmplementeerd.

## Uitgangspunten (vastgesteld met Jeroen)

1. Kleur wordt **automatisch toegekend** bij het aanmaken van een label; de
   (C)PO kan hem daarna aanpassen. Bestaande labels krijgen hun kleur in de
   migratie via hetzelfde algoritme.
2. Kleur is **ondersteunend, nooit dragend**. De labelnaam blijft altijd
   zichtbaar; kleur is redundante versterking, geen informatiedrager. Dat is
   ook de WCAG-eis (1.4.1 Use of Color).
3. De **huisstijlkleur `#F84F39` wordt niet gebruikt** voor labels. Die hue
   (7°) en een bufferzone eromheen zijn uitgesloten uit het palet.
4. Het bestaande contrastprobleem van `#F84F39` (3.40:1, zie
   `docs/accessibility.md`) valt **buiten scope**.
5. Dat er **geen rood** in het palet zit is akkoord. Die hoek van de
   kleurencirkel is bezet door de huisstijl (7°) en `danger` (0°); tien
   overige kleuren zijn ruim voldoende om uit te kiezen. Geen harde eis.

## De ontwerpvraag

De huisstijlkleur zit op hue 7° — bovenop `danger` (0°). De statuskleuren die
al in gebruik zijn (`App\Enums\StateColor`) bezetten:

| rol | hue |
|---|---|
| danger | 0° |
| brand / primary | 7° |
| warning | 32° |
| success | 142° |
| info | 221° |

Een labelkleur mag niet lijken op een statuskleur, anders leest een label als
een waarschuwing. Het palet moet dus afstand houden tot die vijf hues.

## Het algoritme

Kleuren worden **niet met de hand gekozen** maar gegenereerd, zodat ze
onderling en met de huisstijl gecoördineerd zijn.

**Stap 1 — hue-selectie.** Loop de hue-cirkel af en accepteer een hue als hij
≥25° van elke gereserveerde hue ligt én ≥22° van elke al gekozen labelhue.
Dat levert precies 10 hues:

```
57, 79, 101, 167, 189, 246, 268, 290, 312, 334
```

**Stap 2 — OKLCH in plaats van HSL.** Dit is het inhoudelijke punt. Bij een
vaste HSL-lichtheid verschilt de waargenomen helderheid sterk per hue: geel
op L=45% haalde in de test **2.0:1** — onleesbaar — terwijl paars op exact
dezelfde HSL-waarden 5.78:1 haalde. OKLCH is perceptueel uniform, dus één
vaste L geeft over alle hues dezelfde leesbaarheid.

Vier tinten per kleur, elk met vaste OKLCH-coördinaten:

| tint | gebruik | L | C |
|---|---|---|---|
| 600 | tekst op lichte achtergrond | 0.50 | 0.13 |
| 50 | badge-achtergrond, light mode | 0.97 | 0.025 |
| 400 | tekst, dark mode | 0.78 | 0.11 |
| 950 | badge-achtergrond, dark mode | 0.28 | 0.06 |

**Gemeten resultaat** (badge-tekst op eigen tint):

- light mode: 4.87 – 5.80:1
- dark mode: 7.17 – 7.44:1
- **slechtste geval 4.87:1**, ruim boven de AA-norm van 4.5:1

Ter vergelijking: de HSL-variant zakte naar 2.0:1. Dat verschil is de reden
voor OKLCH.

## Het palet

| naam | hue | 600 (tekst) | 50 (bg) | 400 (dark) | 950 (dark bg) |
|---|---|---|---|---|---|
| amber | 57 | `#984B00` | `#FFF1E5` | `#EDA56F` | `#3F2006` |
| olive | 79 | `#8A5700` | `#FEF4E3` | `#DDAF61` | `#382500` |
| moss | 101 | `#746400` | `#F8F6E3` | `#C6BA62` | `#2F2900` |
| emerald | 167 | `#007953` | `#E6FBF2` | `#69CEA9` | `#003222` |
| teal | 189 | `#007972` | `#E3FBF8` | `#52CEC6` | `#00322F` |
| azure | 246 | `#0068A8` | `#E8F7FF` | `#7ABEFA` | `#0A2B44` |
| indigo | 268 | `#445DAD` | `#EEF5FF` | `#99B5FE` | `#1C2746` |
| violet | 290 | `#6453A7` | `#F4F3FF` | `#B6ABF9` | `#292344` |
| purple | 312 | `#7C4A98` | `#FBF1FF` | `#CFA3EB` | `#331F3E` |
| magenta | 334 | `#8E4381` | `#FFEFFC` | `#E39DD4` | `#3A1D35` |

De hex-waarden worden door een generator-script berekend en als constanten
vastgelegd; ze worden niet at runtime uitgerekend.

## Implementatie

### 1. Datamodel

Migratie: `tags.color` toevoegen, `string`, nullable.

Nullable omdat het de enige eerlijke weergave is van "nog geen kleur" — bij
import van een bundel uit een oudere versie ontbreekt de kolom. De render-laag
valt in dat geval terug op grijs.

Backfill in dezelfde migratie: bestaande labels krijgen per organisatie een
kleur volgens het toewijzingsalgoritme, op `name` gesorteerd zodat de uitkomst
deterministisch is.

`Tag::$fillable` uitbreiden met `color`, plus een cast naar de nieuwe enum.

**Transfer werkt automatisch mee**: `EntitySerializer` gebruikt
`getAttributes()`, dus de nieuwe kolom reist mee in export/import zonder
aanpassing in `app/Transfer`.

### 2. Enum

`app/Enums/LabelColor.php`, een backed enum in de stijl van het bestaande
`App\Enums\StateColor`:

```php
enum LabelColor: string
{
    case AMBER = 'amber';
    // ...
}
```

Opgeslagen wordt de **naam**, niet de hex. Zo kan het palet later worden
bijgesteld zonder datamigratie, en blijft de databasewaarde leesbaar.

### 3. Kleurregistratie

In `FilamentServiceProvider::boot()`, naast de bestaande
`FilamentColor::register(['primary' => '#F84F39'])`: de tien paletten
registreren als volledige shade-ramps. Filament verwacht een map `50…950` in
`'r, g, b'`-vorm (zie `vendor/filament/support/src/Colors/Color.php`), niet
één hex.

### 4. Tailwind-safelist — bij nader inzien niet nodig

In het plan stond dat de kleuren met de hand in `theme.css` gezet moesten
worden, naar het voorbeeld van de banner-classes. Dat blijkt hier niet op te
gaan. Filament rendert een badge niet met een class per kleur, maar met een
vaste class (`text-custom-500`) plus een inline CSS-variabele:

- `vendor/filament/support/resources/views/components/badge.blade.php:59-62`
  roept `get_color_css_variables($color, shades: [500])` aan;
- `vendor/filament/support/src/helpers.php:86` maakt daarvan
  `--c-500: var(--amber-500)`;
- `vendor/filament/support/src/Assets/AssetManager.php:258-262` publiceert
  `--amber-50` t/m `--amber-950` voor élke geregistreerde kleur.

De classnamen zijn dus statisch en staan al in de Filament-preset. Registratie
in stap 3 is voldoende; er komt geen regel CSS bij en er is geen risico dat het
lokaal werkt en na een productiebuild niet.

Het banner-precedent blijft geldig voor zijn eigen geval: daar worden wél
`@apply`-classes in PHP samengesteld.

### 5. Toewijzingsalgoritme

`app/Support/LabelColorAssigner.php` (of vergelijkbaar): kies binnen de
organisatie de **minst gebruikte** kleur; bij gelijkspel de laagste
palet-index. Zo blijven kleuren gespreid in plaats van dat één kleur zich
ophoopt.

Aanroep vanuit een `creating`-observer op `Tag`, zodat álle aanmaakpaden
gedekt zijn — inclusief de inline-aanmaak in `TagsInput::createOptionUsing()`,
die anders wordt overgeslagen.

### 6. Bedieningspaneel

**Er is geen nieuw permissierecht nodig.** `TAG_CREATE` / `TAG_VIEW` liggen al
precies bij `CHIEF_PRIVACY_OFFICER` en `PRIVACY_OFFICER` — de (c)po uit de
vraag. Het bestaande `TagResource` ís het bedieningspaneel.

- `TagResourceForm.php` — een `Select` met de tien kleuren, met
  `allowHtml()` zodat elke optie zijn eigen swatch toont.
- `TagResourceTable.php` — `name` als badge in zijn eigen kleur, zodat het
  overzicht meteen laat zien welke kleur waar zit.
- `TagResourceInfolist.php` — idem als badge.

### 7. Weergave op de records

Nu renderen labels als **opsomming in platte tekst**
(`SelectMultipleEntry` = `TextEntry` met `->listWithLineBreaks()->bulleted()`),
niet als badge. Om kleur zichtbaar te maken moet dit een badge worden, op:

- `AvgResponsibleProcessingRecordResourceInfolistSchemas.php:53`
- `AvgProcessorProcessingRecordResourceInfolistSchemas.php:47`
- `WpgProcessingRecordResourceInfolistSchemas.php:46`

De naam blijft daarbij staan — kleur komt erbij, vervangt niets. Dat is
uitgangspunt 2.

### 8. Overig

- `TagFactory`: kleur meegeven.
- Seeders (`DemoContent::TAGS`, `ScreenshotSeeder::TAG_NAMES`): tien labels,
  dus precies één per kleur — geschikt voor de screenshots.

  **Let op:** alle seeders gebruiken `WithoutModelEvents`, waardoor de
  observer daar niet afgaat. In `DemoSeeder` wordt de kleur daarom expliciet
  meegegeven; zonder dat blijft elk demolabel grijs. Dat is dezelfde valkuil
  die in `DpiaScreenshotSeeder.php:106` al staat beschreven voor
  `EntityNumerableObserver`. Vastgesteld door het te reproduceren op een verse
  database, niet door redenering.
- Vertalingen `nl/tag.php` + `en/tag.php`: sleutel `color` en de tien
  kleurnamen.
- Tests uitbreiden in `tests/Feature/Filament/Resources/TagResource/`.

## Verificatie

- `just ci` (phpcs, phpstan level **max**, phpmd, phpunit).
- Een unittest die het contrast van elk paletlid narekent en faalt onder
  4.5:1. Daarmee is het palet niet alleen nu goed, maar blijft het goed.
- `tools/screenshots/a11y.mjs` (axe-core, WCAG A/AA) op een pagina met
  gekleurde labels, om te bevestigen dat er geen nieuwe `color-contrast`-
  bevindingen bij komen.

## Wat hier bewust niet in zit

- **De publieke website.** Labels worden daar niet gepubliceerd — geen enkele
  verwijzing naar tags in `app/Services/StaticWebsite/**` of in het Hugo-thema.
  De site heeft bovendien een eigen, afwijkende primary (`#2563eb`).
- **Het contrastprobleem van de huisstijlkleur.** Bekend en vastgelegd, aparte
  beslissing.
- **`tags.type`.** Bestaat als vertaalsleutel maar is ongebruikt; niet
  aangeraakt.
