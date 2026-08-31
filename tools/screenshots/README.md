# Screenshots voor de handleiding

Genereert de afbeeldingen in `docs/handleiding/imgs/` door de applicatie met
Playwright te bedienen. Bedoeld om vaker te draaien: na een UI-wijziging
regenereer je de figuren in plaats van ze met de hand opnieuw te maken.

## Draaien

Vereist een draaiende, geseede applicatie, met Sail of lokaal (zie
[`docs/local_development_without_docker.md`](../../docs/local_development_without_docker.md)):

```bash
cd src/cms
php artisan db:seed --class=TestDataSeeder
php artisan db:seed --class=ScreenshotSeeder   # deterministische inhoud
php artisan serve --host=127.0.0.1 --port=8000

# In een tweede terminal: nodig voor de exportfiguren, die op een
# achtergrondjob wachten.
php artisan queue:work
```

Daarna:

```bash
cd tools/screenshots
npm install && npx playwright install chromium

CMS_DIR=../../src/cms npm run capture                 # alles, naar docs/handleiding/imgs
CMS_DIR=../../src/cms npm run capture -- --only login # één figuur
CMS_DIR=../../src/cms npm run capture -- --out ./preview  # eerst bekijken
```

Gebruik `--out ./preview` als je het resultaat wilt vergelijken voordat je de
bestaande afbeeldingen overschrijft.

## Hoe het werkt

`capture.mjs` bevat een lijst `FIGURES`; elke figuur beschrijft waar de
afbeelding heen gaat, hoe de applicatie in de juiste toestand komt, en
optioneel op welk element wordt bijgesneden.

```js
{
  name: 'export',
  file: '06_overige_functies/01_avg-responsible-processing-records_export.png',
  auth: true,
  clip: '.fi-main',          // knip bij op dit element, niet op pixelcoördinaten
  async shoot(page) { ... }, // breng de app in de juiste toestand, plaats annotaties
}
```

### Selectors

Gebruik bij voorkeur `getByRole(...)` met de zichtbare of toegankelijke naam.
Dat is niet alleen robuuster dan een CSS-pad, het test ook wat een gebruiker
(en een screenreader) daadwerkelijk waarneemt: wordt een knop hernoemd, dan
faalt de capture — precies het moment waarop de figuur opnieuw gemaakt moet
worden.

Filament levert hiervoor meer aanknopingspunten dan je op het eerste gezicht
ziet. De selectievakjes in tabellen hebben bijvoorbeeld geen `aria-label`, maar
wel een `sr-only`-label met een unieke naam per rij ("Item &lt;key&gt;
selecteren...") tegenover de kop ("Alle items..."). Daarmee is een rij te
selecteren zonder op DOM-volgorde of op Alpine's `x-on:click` te leunen.

Er zijn daarom geen extra `data-*`-attributen aan de applicatie toegevoegd:
rollen en toegankelijke namen dekken de gevallen die ertoe doen, en die zijn
sowieso nuttig voor toegankelijkheid.

Twee keuzes zijn bewust gemaakt:

- **Bijsnijden op elementen, niet op pixels.** Een container als `.fi-main`
  overleeft layoutwijzigingen; een vast rechthoekje knipt na een wijziging
  stilletjes het verkeerde stuk weg.
- **Annotaties verankerd aan selectors.** `annotate.js` tekent pijlen, kaders en
  badges ten opzichte van een element. Verdwijnt dat element, dan volgt een
  duidelijke fout in plaats van een pijl die naar niets wijst.

`annotate.js` wordt via `addInitScript()` in de pagina geïnjecteerd en zit
bewust niet in de applicatiebundel: er kan dus nooit per ongeluk een pijl bij
een echte gebruiker in beeld komen.

### Annotaties

```js
window.__annotate.arrow('#selector', { side: 'left' });  // left|right|top|bottom
window.__annotate.box('#selector');
window.__annotate.badge('#selector', 1);
window.__annotate.redact('#selector');
```

`side` is de kant van het element waar de pijl staat; de pijl wijst altijd naar
het element toe. De accentkleur is `#F84F39`, gelijk aan de huisstijl.

## Inloggen

De applicatie kent geen wachtwoorden. Het script maakt via artisan een
ondertekende magic link aan en doorloopt daarna het tweefactorscherm. Met
`ONE_TIME_PASSWORD_DRIVER=fake` wordt elke code geaccepteerd; zet `OTP_FAKE=0`
om een echte TOTP-code te laten berekenen uit het geseede secret.

## Beperkingen

- Niet alle figuren uit de handleiding zijn geautomatiseerd. `FIGURES` bevat op
  dit moment een deel; de rest staat nog in de originele afbeeldingen.

De figuur `otp-setup` zet de geseede gebruiker tijdelijk terug naar "wel
ingeschakeld, nog niet bevestigd", zodat de verplichte instelpagina in beeld
komt. Dat wordt in een `finally` hersteld; breekt een run halverwege af,
controleer dan `otp_confirmed_at` van de gebruiker voordat je opnieuw begint.

Voor de exportfiguren is `QUEUE_CONNECTION=database` nodig plus een draaiende
worker (zie boven); die instelling staat in
[`.env.nodocker.example`](../../src/cms/.env.nodocker.example). Op de
`sync`-queue verstuurt Filament de voltooiingsmelding als sessiemelding in
plaats van `sendToDatabase()`; de `notifications`-tabel blijft dan leeg en de
melding is niet vast te leggen. Zie
`vendor/filament/actions/src/Exports/Jobs/ExportCompletion.php`.
