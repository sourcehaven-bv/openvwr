# Import / export between organisations

The transfer module (`src/cms/app/Transfer/`) implements "copy & paste" of register content
between organisations via a zip file.

## Export

On the overview pages of the main entity types (AVG verantwoordelijke/verwerker verwerkingen,
WPG verwerkingen, algoritmes and datalekken) rows can be selected; the **Exporteren naar
bestand** bulk action opens a modal listing all related items (verwerkers, ontvangers,
systemen, documenten, doelen, betrokkenen, labels, datalekken, …) with checkboxes.

Confirming dispatches a queued `TransferExportJob` that:

1. walks the selected records and the chosen related items;
2. automatically adds everything those entities depend on: lookup-list values referenced by
   foreign keys, addresses, remarks, FG remarks, stakeholder data items and document files;
3. writes a zip to the `filament` disk under `transfer/exports/`;
4. sends a database notification with a signed download link (valid for 7 days, bound to the
   exporting user). Requires the `export` permission.

## Zip format

```
manifest.json               format name/version, source organisation, entity index
entities/<type>/<uuid>.json one file per entity: attributes, relations, owner, media index
media/<uuid>/<filename>     document files
```

Attributes that never travel: ids, `organisation_id`, entity numbers, `import_id`,
publication/review state and timestamps. Every entity carries an `origin_id` (its original
uuid, or the `origin_id` it was imported with) as stable cross-organisation identity.

## Import

**Functioneel beheer → Importeren uit export** (requires the `import` permission) uploads a
zip, analyses it and shows the contents grouped per type. Per item the user can deselect it,
and when the same content already exists in the organisation (matched by `origin_id`, falling
back to the name/goal/description column) choose a strategy:

- **Overslaan** – keep the existing item and link imported records to it;
- **Overschrijven** – update the existing item with the values from the file;
- **Kopie toevoegen** – create a new item with a ` (kopie)` suffix.

The queued `TransferImportJob` creates everything in dependency order inside one database
transaction, rewrites all foreign keys to the destination organisation (unknown references are
cleared, so no ids from the source organisation can leak in), regenerates entity numbers,
recreates document files and finishes with a notification.

Lookup-list values are always matched by name and created when missing; they are not shown in
the selection screen.

## Limits

`config/transfer.php` caps uploaded zips (`TRANSFER_MAX_ZIPPED_NUMBER_OF_FILES`, default 5000
files, and `TRANSFER_MAX_ZIPPED_FILESIZE`, default 50 MB per file).
