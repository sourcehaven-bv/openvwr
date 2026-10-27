<?php

declare(strict_types=1);

return [
    'content' => [
        'home_title' => 'OpenVWR',
        'privacy_statement' => 'Privacy statement',
    ],

    'public_from_section' => [
        'title' => 'Public availability',
        'subtitle' => 'A processing activity is on the public website when "Publish from" has passed, it has an established version and its organisation is on the public website. After each publication we check that the page is online.',
        'public_history' => 'Overview',
        'public_history_since' => 'Version :version: publicly available since :start',
        'public_history_from_to' => 'Version :version: was publicly available from :start until :end',
    ],

    'publication_state' => [
        'published' => [
            'label' => 'Published',
            'explanation' => 'Version :online_version has been on the public website since :date.',
        ],
        'updating' => [
            'label' => 'Updating',
            'explanation' => 'Version :online_version is online. Established version :established_version is being published; this usually takes a few minutes.',
        ],
        'removing' => [
            'label' => 'Being removed',
            'explanation' => 'Version :online_version is still online but will be removed at the next publication. :reason',
        ],
        'pending' => [
            'label' => 'Publishing',
            'explanation' => 'The processing activity meets all conditions and is being published. This usually takes a few minutes.',
        ],
        'not_confirmed' => [
            'label' => 'Publication not confirmed',
            'explanation' => 'The processing activity meets all conditions, but the check of :date did not find it on the public website. Contact your administrator if this persists.',
        ],
        'scheduled' => [
            'label' => 'Scheduled',
            'explanation' => 'The processing activity will be published on :date.',
        ],
        'not_publishing_disabled' => [
            'label' => 'Not published',
            'explanation' => 'Publishing is disabled for this environment.',
        ],
        'not_public_from_missing' => [
            'label' => 'Not published',
            'explanation' => 'Fill in "Publish from" to publish the processing activity.',
        ],
        'not_no_established_version' => [
            'label' => 'Not published',
            'explanation' => 'There is no established version yet. Only established versions are published.',
        ],
        'not_no_public_content' => [
            'label' => 'Not published',
            'explanation' => 'The established version has no public content. Establish a new version to publish the processing activity.',
        ],
        'not_organisation_not_on_website' => [
            'label' => 'Not published',
            'explanation' => 'The organisation is not on the public website. An administrator can add it under "Website structure".',
        ],
        'not_website_section_not_public' => [
            'label' => 'Not published',
            'explanation' => 'The public website section ":section" has no "Publish from" date. An administrator can set it under "Website structure".',
        ],
    ],

    'publication_version_in_progress' => 'Version :version has the status ":state". Changes become public only once that version is established.',
];
