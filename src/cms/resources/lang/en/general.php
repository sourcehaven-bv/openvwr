<?php

declare(strict_types=1);

return [
    'id' => 'ID',
    'created_at' => 'Created on',
    'review_at' => 'Periodic review',
    'review_at_hint' => 'Please note: if the processing activity goes live on (date), preparations for the periodic review must be made 2.5 years after (date).',
    'review_at_help' => 'Date on which this processing activity must be reassessed to '
        . 'check whether the details are still correct. By default 2.5 years after going live; '
        . 'around that date the processing activity appears in the overview of activities due for review.',
    'updated_at' => 'Edited on',
    'public_from' => 'Publish from',
    'public_from_set_now' => 'Publish from now',
    'published_at' => 'Link to public page',
    'now' => 'Now',
    'data_collection_source' => 'Primary / Secondary',
    'data_collection_source_help_short' => 'Primary: belongs to the organisation\'s own core task. '
        . 'Secondary: business operations (such as HR or ICT), often recurring and standard.',
    'data_collection_source_help' => 'A primary processing activity belongs to your organisation\'s core task: '
        . 'the work the organisation exists for. A secondary processing activity belongs to the business '
        . 'operations that support that work, such as HR, finance, ICT and facility services. Secondary '
        . 'processing activities occur more often and usually have a standard character. The distinction can '
        . 'play a part in where you publish the processing activity; follow your organisation\'s publication '
        . 'policy in this.',
    'attention' => 'Please note',

    'all' => 'All',
    'add' => 'Add',
    'and' => 'and',
    'cancel' => 'Cancel',
    'close' => 'Close',
    'delete' => 'Delete',
    'deleted' => 'Deleted',
    'disabled' => 'Disabled',
    'download' => 'Download',
    'enabled' => 'Enabled',
    'error' => 'Error',
    'export' => 'Export',
    'fg_remarks' => 'DPO remarks (only visible to DPOs)',
    'none_selected' => 'none',
    'save' => 'Save',
    'saved' => 'Saved',

    'help_country' => 'The countries outside the EEA to which personal data is transferred.',

    'picker_recent' => 'Recently edited',

    'parent' => 'Parent processing activity',
    'parent_hint_icon_text' => 'If this processing activity is a sub-activity of a parent processing activity, you can indicate the parent activity here. All sub-activities can be found with the parent activity in the "Sub-activities" table.',
    'parent_help' => 'Only fill in if this processing activity is part of a larger '
        . 'processing activity. In that case choose the overarching activity; this activity is then '
        . 'shown in the "Sub-activities" table of that activity. '
        . 'Leave empty for a standalone processing activity.',
    'child' => 'Sub-activity',
    'children' => 'Sub-activities',
    'children_help' => 'The processing activities that fall under this processing activity. Only '
        . 'standalone processing activities can be linked; after unlinking, '
        . 'the processing activity is standalone again.',

    'data_loss_confirm_title' => 'Are you sure?',
    'data_loss_confirm_submit' => 'Yes, delete data',
    'data_loss_confirm_cancel' => 'Cancel',

    'name' => 'Name',
    'description' => 'Description',
    'import_id' => 'Import ID',
    'attachments' => 'Attachments',

    'yes' => 'Yes',
    'no' => 'No',
    'unknown' => 'Unknown',

    'create_form_action_label' => 'Save',
    'create_another_form_action_label' => 'Save & create new',
    'number_create_failed' => 'Generating a (unique) number failed, please try again',

    'manual' => 'Manual',
    'go_to_public_page' => 'View on the public website',
    'edit' => 'Edit',
    'onepage_nav_label' => 'Jump to section',

    'country' => 'Countries',
    'country_other' => 'Other, namely:',
];
