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

`transfer` staat los van `filament` omdat een transfer-bundel de volledige
registerinhoud van een organisatie bevat. De `filament`-disk is `public`; op een
bucket zou dat betekenen dat die zips voor iedereen leesbaar zijn. De
`transfer`-disk is daarom `private`.

## Configuratie

```dotenv
FILESYSTEM_SHARED_DRIVER=s3
AWS_ENDPOINT=http://minio:9000
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=eu-central-1
AWS_USE_PATH_STYLE_ENDPOINT=true
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
