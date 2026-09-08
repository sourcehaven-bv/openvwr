<?php

declare(strict_types=1);

return [
    'model_singular' => 'Mappingprofiel',
    'model_plural' => 'Mappingprofielen',

    'title' => 'Import',
    'help' => 'Upload een OpenVWR-export (zip) of een Excel-/CSV-bestand. Bij een export wordt getoond wat erin zit; bij Excel of CSV worden de kolommen geanalyseerd zodat u de mapping kunt controleren voordat er iets wordt opgeslagen.',
    'target' => 'Importeren als',
    'file' => 'Bestand (xlsx of csv)',
    'file_any' => 'Bestand',
    'target_help' => 'Alleen nodig voor een Excel- of CSV-bestand. Een OpenVWR-export (zip) bepaalt dit zelf.',

    'archive_heading' => 'OpenVWR-export gevonden',
    'archive_body' => 'Dit bestand bevat gegevens in het formaat van OpenVWR zelf. De kolommen hoeven niet gekoppeld te worden; controleer wat er gevonden is en bevestig.',
    'archive_row' => ':register — :count records',
    'archive_apply' => 'Importeren',
    'archive_empty' => 'In dit bestand zijn geen herkenbare registers gevonden.',
    'analyse' => 'Analyseren',
    'read_failed' => 'Bestand kon niet gelezen worden',
    'session_expired' => 'De geüploade gegevens zijn niet meer beschikbaar. Upload het bestand opnieuw.',
    'import_failed' => 'De import is mislukt. Er is niets opgeslagen.',

    'recognised_heading' => 'Bekende indeling herkend',
    'recognised_body' => 'De kolommen komen overeen met het opgeslagen profiel ":name". De mapping is alvast ingevuld.',

    'review_heading' => 'Mapping controleren',
    'review_body' => ':rows rijen gevonden. Herkende kolommen zijn alvast ingevuld; controleer ze en vul de rest aan. Kolommen op "niet importeren" blijven buiten de import.',

    'rows_heading' => 'Rijen samenvoegen',
    'rows_body' => 'Staat één record in het bestand over meerdere rijen, bijvoorbeeld een rij per systeem of per doel, kies dan de kolom die het record aanduidt. Rijen met dezelfde waarde daarin worden één record.',
    'rows_proposal' => '{1} Deze :rows rijen lijken samen één record: rijen met dezelfde ":column" horen bij elkaar. Controleer de kolom hieronder.|[2,*] Deze :rows rijen lijken :records records: rijen met dezelfde ":column" horen bij elkaar. Controleer de kolom hieronder.',
    'rows_column' => 'Kolom die het record aanduidt',
    'rows_none' => '— iedere rij is een eigen record —',
    'rows_effect' => ':records records uit :rows rijen. Koppelingen en notities worden uit alle rijen van een record verzameld; een gewoon veld moet in die rijen dezelfde waarde hebben.',
    'column_source' => 'Kolom in bestand',
    'column_example' => 'Voorbeeldwaarde',
    'column_target' => 'Veld in OpenVWR',
    'column_transform' => 'Type',
    'read_as' => 'Wordt gelezen als: :transform',

    'true_date_intro' => 'Deze kolom bevat ja/nee, maar het doelveld is een datum. Welke datum hoort bij "ja"?',
    'true_date_today' => 'Datum van de import',
    'true_date_fixed' => 'Vaste datum',
    'true_date_no' => '"nee" laat het veld leeg.',
    'date_format_intro' => 'Deze kolom bevat ":sample". Welke datum is dat?',
    'date_format_example' => ':sample wordt gelezen als :date',
    'date_format_missing' => 'Kies eerst het datumformaat voor kolom ":column".',
    'duplicate_target' => 'Het veld ":field" is voor meer dan één kolom gekozen (":columns"). Een veld neemt één kolom; zet de andere op "niet importeren" of op Notitie.',
    'date_order' => [
        'day_first' => 'dag-maand-jaar',
        'month_first' => 'maand-dag-jaar',
        'year_first' => 'jaar-maand-dag',
    ],
    'column_row' => 'Rij',
    'column_reason' => 'Reden',

    'ignore' => '— niet importeren —',
    'field_import_id' => 'Bronkenmerk (nummer uit het bronsysteem)',
    'field_remarks' => 'Notitie',

    'group_none' => '',
    'group_source' => 'Herkomst',
    'group_other' => 'Overig',
    'group_relations' => 'Koppelingen',
    'group_lookups' => 'Opzoeklijsten',
    'group_notes' => 'Notities',

    'status_open' => 'Nog geen keuze gemaakt',
    'status_suggested_strong' => 'Automatisch ingevuld',
    'status_suggested_weak' => 'Voorstel — controleer',
    'status_manual' => 'Zelf gekozen',
    'all_settled' => 'Alle kolommen zijn automatisch herkend. Controleer ze hieronder of ga door.',
    'settled_heading' => 'Uit opgeslagen profiel (:count)',
    'settled_body' => 'Deze kolommen komen uit het herkende profiel. Openklappen om aan te passen.',

    'dry_run' => 'Proefdraaien',
    'dry_run_heading' => 'Resultaat proefdraai',
    'dry_run_summary' => ':fits rijen passen, :issues rijen hebben aandacht nodig.',
    'dry_run_done' => 'Proefdraai klaar: :fits passen, :issues met aandachtspunten.',

    'save_profile_heading' => 'Mapping bewaren voor hergebruik',
    'save_profile_placeholder' => 'Naam, bijv. "Zenya VIM-export maandelijks"',

    'apply' => 'Importeren',
    'applied' => ':count rijen geïmporteerd.',
    'applied_with_failures' => ':count rijen geïmporteerd, :failed rijen konden niet worden opgeslagen.',
    'skipped_existing' => ':count rijen waren al eerder geïmporteerd (zelfde bronkenmerk) en zijn overgeslagen.',
    'failed_rows_heading' => 'Niet opgeslagen',
    'failed_rows_body' => 'Deze rijen konden niet worden opgeslagen. De overige rijen zijn wel geïmporteerd.',
    'restart' => 'Opnieuw beginnen',

    'new_entities_heading' => 'Nieuw aangemaakt',
    'new_entities_body' => 'Deze kwamen niet voor in het register en zijn aangemaakt. Controleer of het geen dubbelen zijn.',
    'unresolved_entities_heading' => 'Niet gevonden koppelingen',
    'unresolved_entities_body' => 'Deze namen komen niet voor in het register. Ze zijn niet aangemaakt: leg de koppeling na de import handmatig, of corrigeer de naam in het bronbestand.',

    'ambiguous_entities_heading' => 'Meerdere records met dezelfde naam',
    'ambiguous_entities_body' => 'Het register bevat al dubbelen. De rijen zijn gekoppeld aan het oudste record; voeg de dubbelen samen zodat de koppeling klopt.',
    'ambiguous_entity' => '":name" komt :count keer voor.',

    'fuzzy_entities_heading' => 'Gekoppeld op afwijkende schrijfwijze',
    'fuzzy_entities_body' => '":source" is gekoppeld aan het bestaande ":matched".',

    'result_heading' => 'Import afgerond',
    'result_body' => ':count rijen geïmporteerd. :issues rijen zijn overgeslagen en kunnen na aanpassing opnieuw worden aangeboden.',
    'result_failed' => ':count rijen konden niet worden opgeslagen; de reden staat hieronder.',

    'transform' => [
        'text' => 'Tekst',
        'date' => 'Datum',
        'boolean' => 'Ja/nee',
        'integer' => 'Getal',
        'string_list' => 'Lijst (regel per waarde)',
        'relation' => 'Gekoppeld record (wordt opgezocht of aangemaakt)',
        'remark' => 'Notitie bij het record (kolomnaam: waarde)',
    ],


    'issue' => [
        'missing_required' => 'Verplicht veld leeg: :fields',
        'not_convertible' => 'Kolom ":column" bevat een waarde die niet als :transform gelezen kan worden',
        'too_long' => 'Veld ":field" is langer dan :max tekens',
        'not_an_option' => 'Kolom ":column" bevat een waarde die geen keuze is van ":field"',
        'rows_disagree' => 'Rijen :rows horen bij één record maar verschillen in kolom ":column"; een record kan daar maar één waarde hebben',
        'write_failed' => 'De rij kon niet worden opgeslagen; de details staan in het logboek.',
        'write_failed_too_long' => 'Een waarde is te lang voor het veld in OpenVWR.',
        'write_failed_out_of_range' => 'Een getal valt buiten het bereik van het veld.',
        'write_failed_wrong_type' => 'Een waarde heeft niet het type dat het veld verwacht.',
        'write_failed_required' => 'Een verplicht veld is leeg.',
        'write_failed_reference' => 'Een koppeling verwijst naar een record dat niet bestaat.',
        'write_failed_duplicate' => 'Het record bestaat al.',
    ],

    'error' => [
        'unreadable' => 'Het bestand kon niet gelezen worden. Controleer of het een geldig xlsx- of csv-bestand is.',
        'unreadable_archive' => 'Het zip-bestand kon niet gelezen worden.',
        'unsupported_type' => 'Alleen xlsx- en csv-bestanden worden ondersteund.',
        'no_header' => 'Het bestand heeft geen kopregel met kolomnamen.',
        'no_rows' => 'Het bestand bevat alleen een kopregel en geen gegevens.',
        'duplicate_column' => 'De kolomnaam ":column" komt meer dan één keer voor. Geef iedere kolom een eigen naam.',
        'column_conflict' => 'De kolommen ":column" en ":other" overlappen: een kolom kan niet tegelijk een waarde en een groep zijn.',
        'too_many_rows' => 'Het bestand bevat meer dan :max rijen. Splits het in kleinere bestanden.',
        'archive_too_many_files' => 'Het zip-bestand bevat te veel bestanden.',
        'archive_entry_too_large' => 'Een bestand in het zip-bestand is groter dan toegestaan.',
    ],
];
