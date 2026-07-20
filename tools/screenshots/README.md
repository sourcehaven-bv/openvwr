# Screenshots voor de handleiding

Genereert de afbeeldingen in `docs/handleiding/imgs/` door de applicatie met
Playwright te bedienen. Bedoeld om vaker te draaien: na een UI-wijziging
regenereer je de figuren in plaats van ze met de hand opnieuw te maken.

## Draaien

Vereist een draaiende, geseede applicatie (zie
[`docs/local_development_without_docker.md`](../../docs/local_development_without_docker.md)):

```bash
cd src/cms
php artisan db:seed --class=TestDataSeeder
php artisan db:seed --class=ScreenshotSeeder   # deterministische inhoud
php artisan serve --host=127.0.0.1 --port=8000
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
  file: '05_overige_functies/01_avg-responsible-processing-records_export.png',
  auth: true,
  clip: '.fi-main',          // knip bij op dit element, niet op pixelcoördinaten
  async shoot(page) { ... }, // breng de app in de juiste toestand, plaats annotaties
}
```

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
- De exportmelding (`05_overige_functies/02_..._export_complete.png`) werkt
  lokaal niet: de `filament`-disk is een S3-disk die naar MinIO wijst, en die
  draait niet in de Docker-loze setup. De export start wel, maar levert nul
  rijen op. Draai die figuur op de Docker-setup, of configureer MinIO lokaal.
