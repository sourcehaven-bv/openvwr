# Toegankelijkheid

De applicatie is geautomatiseerd te controleren met
[axe-core](https://github.com/dequelabs/axe-core), via het Playwright-script in
[`tools/screenshots/a11y.mjs`](../tools/screenshots/a11y.mjs). Het script deelt
de login en navigatie met de screenshot-capture: de browser bezoekt die
pagina's toch al.

```bash
cd tools/screenshots
CMS_DIR=../../src/cms node a11y.mjs                       # samenvatting per pagina
CMS_DIR=../../src/cms node a11y.mjs --json report.json    # volledig rapport
CMS_DIR=../../src/cms node a11y.mjs --impact critical     # alleen critical
```

Het script controleert op WCAG 2.0/2.1 niveau A en AA, en eindigt met een
exitcode ≠ 0 zodra er overtredingen zijn op of boven de `--impact`-drempel
(standaard `serious,critical`). Daarmee is het geschikt als CI-gate zodra de
huidige bevindingen zijn opgelost of expliciet geaccepteerd.

## Bevindingen (eerste meting, 10 pagina's)

| Regel | Ernst | Nodes | Oorzaak |
|---|---|---|---|
| `color-contrast` | serious | 101 | Huisstijl, zie hieronder |
| `link-name` | serious | 88 | Filament-tabelrijen |
| `listitem` / `list` | serious | 31 | Filament-formulieren |
| `aria-allowed-attr` | critical | 18 | Filament-formuliervelden |
| `dlitem` / `definition-list` | serious | 18 | Filament-infolist |
| `button-name` | critical | 10 | Filament-sectiekoppen |
| `aria-valid-attr-value` | critical | 3 | Filament-tabs |
| `nested-interactive` | serious | 2 | Filament-formuliervelden |

Op één na wijzen alle bevindingen naar markup van Filament zelf (de selectors
beginnen met `fi-`), niet naar code in deze repository. Die zijn dus vooral
relevant als upstream-issue of bij een Filament-upgrade.

### Contrast van de primaire kleur

De uitzondering is het belangrijkste punt, omdat het een eigen keuze is en niet
die van een framework:

| Combinatie | Ratio | WCAG AA (4.5:1 normaal, 3.0:1 groot) |
|---|---|---|
| Wit op `#F84F39` | **3.40:1** | Onvoldoende voor normale tekst |
| `#111827` op `#F84F39` | 5.21:1 | Voldoende |

`#F84F39` is de primaire kleur (zie `FilamentServiceProvider`), en witte tekst
daarop haalt de AA-norm voor normale tekst niet. Dat raakt elke primaire knop in
de applicatie.

Mogelijke richtingen, in volgorde van impact op de huisstijl:

1. Donkere tekst op de primaire kleur (5.21:1) — kleinste ingreep, maar wijkt af
   van de gebruikelijke weergave van een primaire knop.
2. Een iets donkerder variant van `#F84F39` voor knopachtergronden, met behoud
   van de huidige kleur voor accenten en grotere elementen.
3. Accepteren en vastleggen als bewuste afwijking, eventueel met een
   toegankelijkheidsverklaring.

Dit is een ontwerpbeslissing; dit document doet er geen uitspraak over.

## Beperkingen

- Automatische controles vangen ongeveer een derde van de WCAG-criteria. Zaken
  als toetsenbordnavigatie, focusvolgorde, en de begrijpelijkheid van
  foutmeldingen vragen handmatige toetsing.
- De gecontroleerde set pagina's staat in `PAGES` in `a11y.mjs` en dekt de
  hoofdschermen, niet elk formulier of elke modal.
