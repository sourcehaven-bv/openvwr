# Screenshots voor de handleiding

Genereert de afbeeldingen in `src/cms/public/handleiding/` door de applicatie met
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

CMS_DIR=../../src/cms npm run capture                 # alles, naar src/cms/public/handleiding
CMS_DIR=../../src/cms npm run capture -- --only login # één figuur
CMS_DIR=../../src/cms npm run capture -- --out ./preview  # eerst bekijken
```

Gebruik `--out ./preview` als je het resultaat wilt vergelijken voordat je de
bestaande afbeeldingen overschrijft.

Een volledige run duurt enkele minuten. Zet hem op een laptop achter
`caffeinate`, anders valt de machine op accu tussentijds in slaap:

```bash
caffeinate -dimsu just screenshots
```

Slaapt hij toch, dan lopen de Playwright-timeouts door terwijl het proces
bevroren is en faalt de rest van de run met timeouts die niets met de figuren
te maken hebben. Losse figuren slagen dan nog wél, wat het spoor misleidend
maakt: zoek in dat geval eerst naar gaten van een kwartier in het serverlog
(`pmset -g log | grep Sleep`) voordat je de tooling verdenkt.

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

### Maskeren

De figuren komen in `src/cms/public/` te staan en worden dus zonder
authenticatie geserveerd. De otp-figuur toont noodzakelijkerwijs een scanbare
QR-code met de bijbehorende sleutel; die worden daarom vóór het maken van de
schermafdruk afgedekt met een plaatshouder.

Een figuur declareert dat met `mask`:

```js
mask: [
  { selector: '[data-screenshot-mask="qr"]', text: 'QR-code\nverschijnt hier' },
  { selector: '[data-screenshot-mask="secret"]', text: 'Sleutel: XXXX XXXX XXXX XXXX' },
],
```

De plaatshouder wordt op de afmetingen van het element zelf gezet, net als de
andere annotaties. Dat is hier niet alleen consistentie: de sleutel is tekst van
variabele breedte in een `break-words`-alinea, dus een brede sleutel maakt de
regel breder én laat hem doorlopen op een tweede regel. Een vaste rechthoek laat
dan een deel van het geheim staan.

Het masker gaat er vóór de schermafdruk overheen, dus het geheim komt niet in de
afbeelding terecht — er valt achteraf niets meer te verwijderen, en er is geen
omkeerbare bewerking (zoals blur) die iemand ongedaan kan maken.

Daarna wordt gecontroleerd of het masker het element daadwerkelijk dekt; zo
niet, dan faalt de capture. Een masker dat er stilletjes naast zit is erger dan
geen: dat leest als "geregeld" terwijl het geheim alsnog gepubliceerd wordt.

De twee elementen hebben hiervoor een `data-screenshot-mask`-attribuut in
`one-time-password.blade.php`. Dat is bewust een uitzondering op de regel
hieronder: bij de andere figuren mag een selector die niet meer klopt een
mislukte capture opleveren, maar hier moet volstrekt duidelijk zijn — ook vanuit
de blade — dat deze elementen niet gepubliceerd mogen worden.

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

Er zijn daarom vrijwel geen extra `data-*`-attributen aan de applicatie
toegevoegd: rollen en toegankelijke namen dekken de gevallen die ertoe doen, en
die zijn sowieso nuttig voor toegankelijkheid. De uitzondering is
`data-screenshot-mask` (zie [Maskeren](#maskeren)), waar een mislukte selector
niet alleen een lelijke figuur maar een gepubliceerd geheim zou opleveren.

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
window.__annotate.mask('#selector', 'QR-code\nverschijnt hier');  // box: false voor tekst
```

`redact` legt een grijze balk over iets; `mask` zet er een plaatshouder mét
uitleg overheen, voor inhoud die helemaal niet gepubliceerd mag worden.
Gebruik in figuren bij voorkeur de declaratieve `mask`-eigenschap hierboven:
die controleert ook of het element daadwerkelijk gedekt is.

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
