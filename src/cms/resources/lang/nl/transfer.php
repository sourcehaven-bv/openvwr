<?php

declare(strict_types=1);

return [
    // export
    'export_action' => 'Exporteren naar bestand',
    'export_modal_heading' => 'Exporteren naar bestand',
    'export_modal_description' => 'De geselecteerde items worden samen met de aangevinkte gerelateerde items in een zip-bestand gezet. Dit bestand kan in een andere organisatie worden geïmporteerd.',
    'export_summary' => 'Geselecteerde items: :count. Kies hieronder welke gerelateerde items mee worden geëxporteerd.',
    'export_submit' => 'Exporteren',
    'export_started' => 'Export gestart',
    'export_started_body' => 'De export van :count item(s) is gestart. U ontvangt een melding met een downloadlink zodra het bestand klaar staat.',
    'export_ready' => 'Export gereed',
    'export_ready_body' => 'De export van :count item(s) is gereed.',
    'download' => 'Downloaden',

    // import
    'import_page_title' => 'Importeren uit export',
    'import_help' => 'Importeer een zip-bestand dat via "Exporteren naar bestand" in een (andere) organisatie is gemaakt. Na het analyseren van het bestand kunt u kiezen welke items u wilt importeren.',
    'import_file' => 'Exportbestand (zip)',
    'analyse' => 'Bestand analyseren',
    'preview_heading' => 'Inhoud van het exportbestand',
    'preview_source' => 'Geëxporteerd vanuit ":organisation" op :date.',
    'exists_unchanged' => 'Bestaat al en is identiek: ":name" — niets te doen',
    'copy_all_unchanged' => 'Alle geselecteerde items bestaan al en zijn identiek. Er is niets te kopiëren.',
    'exists_edited' => 'Bestaat al met lokale wijzigingen: ":name" — kies wat te doen',
    'strategy_skip' => 'Overslaan (bestaande gebruiken)',
    'strategy_overwrite' => 'Overschrijven',
    'strategy_copy' => 'Kopie toevoegen',
    'lookup_note' => 'Referentielijst-waarden (zoals diensten, documenttypen en thema\'s) worden automatisch op naam gekoppeld en waar nodig aangemaakt.',
    'import_submit' => 'Importeren',
    'cancel' => 'Annuleren',
    'import_started' => 'Import gestart',
    'import_invalid_file' => 'Het bestand kan niet worden gelezen',
    'import_failed' => 'Import mislukt',
    'import_finished' => 'Import afgerond',
    'import_finished_body' => 'Aangemaakt: :created, overschreven: :overwritten, overgeslagen: :skipped.',
    'copy_suffix' => ' (kopie)',

    // copy to another organisation
    'copy_action' => 'Kopiëren naar organisatie',
    'copy_page_title' => 'Kopiëren naar organisatie',
    'copy_target_heading' => 'Doelorganisatie',
    'copy_target_description' => 'Kies de organisatie waarnaar de geselecteerde items worden gekopieerd, en welke gerelateerde items meegaan.',
    'copy_pick_target' => 'Kies een doelorganisatie…',
    'copy_no_targets' => 'U heeft in geen enkele andere organisatie rechten om te importeren.',
    'copy_related_heading' => 'Gerelateerde items om mee te kopiëren',
    'copy_analyse' => 'Controleren',
    'copy_preview_heading' => 'Te kopiëren items',
    'copy_preview_description' => 'Bestaande items die identiek zijn worden overgeslagen. Voor items met lokale wijzigingen kiest u zelf wat er gebeurt.',
    'copy_submit' => 'Kopiëren',
    'copy_back' => 'Terug',
    'copy_failed' => 'Kopiëren mislukt',
    'copy_finished' => 'Kopiëren afgerond',
];
