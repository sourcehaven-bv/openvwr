<?php

declare(strict_types=1);

return [
    // export
    'export_action' => 'Export to file',
    'export_modal_heading' => 'Export to file',
    'export_modal_description' => 'The selected items are placed in a zip file together with the selected related items. This file can be imported into another organisation.',
    'export_summary' => 'Selected items: :count. Choose below which related items are exported along with them.',
    'export_submit' => 'Export',
    'export_started' => 'Export started',
    'export_started_body' => 'The export of :count item(s) has started. You will receive a notification with a download link as soon as the file is ready.',
    'export_ready' => 'Export ready',
    'export_ready_body' => 'The export of :count item(s) is ready.',
    'download' => 'Download',

    // import
    'import_page_title' => 'Import from export',
    'import_help' => 'Import a zip file created via "Export to file" in a (different) organisation. After analysing the file you can choose which items you want to import.',
    'import_file' => 'Export file (zip)',
    'analyse' => 'Analyse file',
    'preview_heading' => 'Contents of the export file',
    'preview_source' => 'Exported from ":organisation" on :date.',
    'exists_unchanged' => 'Already exists and is identical: ":name" — nothing to do',
    'copy_all_unchanged' => 'All selected items already exist and are identical. There is nothing to copy.',
    'exists_edited' => 'Already exists with local changes: ":name" — choose what to do',
    'strategy_skip' => 'Skip (use existing)',
    'strategy_overwrite' => 'Overwrite',
    'strategy_copy' => 'Add copy',
    'lookup_note' => 'Reference list values (such as services, document types and themes) are automatically matched by name and created where necessary.',
    'import_submit' => 'Import',
    'cancel' => 'Cancel',
    'import_started' => 'Import started',
    'import_invalid_file' => 'The file cannot be read',
    'import_failed' => 'Import failed',
    'import_finished' => 'Import completed',
    'import_finished_body' => 'Created: :created, overwritten: :overwritten, skipped: :skipped.',
    'copy_suffix' => ' (copy)',

    // copy to another organisation
    'copy_action' => 'Copy to organisation',
    'copy_page_title' => 'Copy to organisation',
    'copy_target_heading' => 'Target organisation',
    'copy_target_description' => 'Choose the organisation to which the selected items are copied, and which related items are included.',
    'copy_pick_target' => 'Choose a target organisation…',
    'copy_no_targets' => 'You do not have import rights in any other organisation.',
    'copy_related_heading' => 'Related items to copy along',
    'copy_analyse' => 'Check',
    'copy_preview_heading' => 'Items to copy',
    'copy_preview_description' => 'Existing items that are identical are skipped. For items with local changes you decide what happens.',
    'copy_submit' => 'Copy',
    'copy_back' => 'Back',
    'copy_failed' => 'Copy failed',
    'copy_finished' => 'Copy completed',
];
