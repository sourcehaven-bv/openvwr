# Objectopslag (S3 / MinIO)

De gedeelde disks staan standaard op de lokale schijf. Objectopslag is *opt-in*:
zet `FILESYSTEM_SHARED_DRIVER=s3` en dezelfde disks draaien op een
S3-compatibele bucket. Dat is bedoeld voor deployments met meerdere nodes, waar
uploads niet aan één machine vast mogen zitten.

Zonder die variabele verandert er niets — lokaal, in CI en in bestaande
deployments blijft alles op schijf staan.

## De gedeelde disks

| Disk | Bucket-variabele | Inhoud |
|---|---|---|
| `media-library` | `UPLOADS_BUCKET` (`uploads`) | Bijlagen bij documenten |
| `filament` | `EXPORTS_BUCKET` (`exports`) | Filament-exports (xlsx) |
| `transfer` | `TRANSFER_BUCKET` (`transfer`) | Import/export-bundels tussen organisaties |

Alle gedeelde disks zijn `private`. Op S3 wordt `public` een `public-read`
object-ACL; een bestand is dan zonder inloggen op te halen. Exports bevatten
registergegevens, dus ook de `filament`-disk is `private`. Downloads lopen via
de route van Filament, die controleert of de gebruiker de export mag zien.

`transfer` staat los van `filament` omdat een transfer-bundel de volledige
registerinhoud van een organisatie bevat.

## Versleuteling at rest

POLICY-014 eist dat vertrouwelijke gegevens versleuteld zijn opgeslagen, met
minimaal AES-256. De gedeelde disks bevatten bijlagen en volledige
registerexports, dus dat geldt hier.

De app schrijft daarom elk object met server-side encryptie (SSE-S3, `AES256`).
Dat staat standaard aan via `AWS_SERVER_SIDE_ENCRYPTION=AES256`. Een nieuwe
bucket krijgt bij `storage:setup-buckets` dezelfde versleuteling als standaard,
zodat ook objecten die buiten de app worden geschreven versleuteld zijn.
Bestaande buckets blijven ongewijzigd.

- **AWS S3** ondersteunt `AES256` zonder extra instellingen.
- **MinIO** weigert versleutelde uploads zonder KMS-sleutel. Zet
  `MINIO_KMS_SECRET_KEY` (formaat `<naam>:<base64 van 32 willekeurige bytes>`)
  of koppel een externe KMS. Docker Compose en CI gebruiken een vaste
  ontwikkelsleutel; gebruik die nooit in productie.
- **Andere S3-compatibele servers**: controleer dat ze SSE-S3 ondersteunen
  voordat je ze koppelt.

`AWS_SERVER_SIDE_ENCRYPTION=none` zet de versleuteling uit. Dat is alleen
bedoeld voor een ontwikkelserver zonder KMS, zoals de Homebrew-minio uit
`just setup-native-object-storage`. Gebruik het niet in productie.
Een `.env.native` van vóór deze instelling mist die regel; voeg hem zelf toe.

## Downloads van bijlagen

Bijlagen downloaden gaat via `GET /media/{media}` (`PrivateMediaController`).
Die route controleert bij elk verzoek of de gebruiker is ingelogd, zet de
organisatie, controleert `can('view')` en of het bestand gevalideerd is. Daarna
streamt de app het bestand van de disk naar de browser. Het geheugengebruik
blijft daarbij laag. Wel is een PHP-worker bezet zolang de overdracht duurt.
Bijlagen mogen 20 MB zijn; de PHP-uploadgrens van een deployment kan hoger
liggen, bijvoorbeeld 100 MB.

### Waarom (nog) geen presigned URLs

Een presigned URL laat de browser het bestand direct uit de bucket halen. We
doen dat bewust niet:

- Een presigned URL is een bearer token. Wie de URL heeft, kan het bestand
  ophalen tot de URL verloopt. Intrekken kan niet.
- De browser laadt het bestand dan van het domein van de opslag. Dat vraagt
  aanpassingen in de CSP (`src/cms/app/Http/CspPolicy.php`) en CORS-instellingen
  op de bucket, per deployment.
- De app kan alleen vastleggen dat een URL is uitgegeven, niet dat het bestand
  echt is gedownload.

SSE-S3 staat presigned URLs niet in de weg; dat is dus geen reden.

### Als de workerbelasting een probleem wordt

Pak dit pas op als de belasting gemeten een probleem is. Er zijn twee opties.

1. **Offload via de proxy (voorkeur).** De app controleert de rechten en geeft
   een `X-Accel-Redirect` terug naar een interne nginx-location. Nginx haalt het
   bestand op uit de opslag, bijvoorbeeld via een kortlevende presigned URL die
   de browser nooit ziet. Dit vereist nginx vóór de app, en dat hangt af van de
   hosting.
2. **Presigned redirect, opt-in.** De app controleert de rechten en stuurt de
   browser door naar een presigned URL die na 1 tot 5 minuten verloopt. Forceer
   een download (`Content-Disposition: attachment`) en log het uitgeven van de
   URL. Pas ook de CSP en de CORS-instellingen van de bucket aan.

## Configuratie

```dotenv
FILESYSTEM_SHARED_DRIVER=s3
AWS_ENDPOINT=http://minio:9000
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=eu-central-1
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_SERVER_SIDE_ENCRYPTION=AES256
```

Daarna moeten de buckets bestaan:

```bash
php artisan storage:setup-buckets
```

Het commando is idempotent en doet niets zolang de disks op `local` staan, dus
het kan zonder voorwaarden in een deploy-script.

## Lokaal draaien

**Met Docker** — minio zit achter een compose-profile en start dus niet mee met
een gewone `sail up`:

```bash
just minio-up      # start minio en maakt de buckets aan
just minio-down    # stopt minio, volume blijft staan
```

De console draait op http://localhost:9001 (`minioadmin` / `minioadmin`).

**Zonder Docker** — de setup installeert minio als brew-service en zet de
`.env` meteen goed:

```bash
just setup-native-object-storage
```

Op een bestaande installatie kan het ook los:

```bash
just minio-native-up
just minio-buckets
```

`just doctor-native` controleert daarna of er iets op poort 9000 luistert,
maar alleen als de `.env` daadwerkelijk `FILESYSTEM_SHARED_DRIVER=s3` zet.

> De Homebrew-formule `minio` is upstream gearchiveerd en verdwijnt op
> 2027-02-17. Er zit niets minio-specifieks in de code: elke S3-compatibele
> server op dezelfde poort werkt, bijvoorbeeld `seaweedfs` of `garage`.

## Waarom de applicatiecode dit moet weten

`Filesystem::path()` bestaat alleen echt op de lokale driver. Op s3 gooit die
geen fout maar geeft hij de kale object-key terug, wat als relatief pad wordt
geïnterpreteerd. Code die dat pad aan `ZipArchive` of `response()->download()`
geeft, faalt dus stil: het bestand belandt ergens anders, of de download geeft
een 404.

Alles wat met bundels werkt, loopt daarom via `App\Transfer\TransferBundleStorage`.
Die schrijft en leest via streams en zet een tijdelijke lokale kopie klaar waar
`ZipArchive` een echt pad nodig heeft. Mediabestanden worden via
`Storage::disk($media->disk)` en `getPathRelativeToRoot()` gelezen, niet via
`Media::getPath()` — dat laatste kent dezelfde valkuil.

De CI-job `object-storage` draait de opslaggevoelige suites tegen een echte
minio, zodat dit pad niet ongemerkt kan verrotten.
