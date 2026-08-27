<?php

declare(strict_types=1);

return [
    'model_singular' => 'Organisation',
    'model_plural' => 'Organisations',
    'table_empty_heading' => 'No organisations',

    'section_general' => 'General',
    'section_prefix' => 'Prefix',
    'section_public' => 'Public website',

    'user_attach' => 'Link users',
    'user_attach_description' => 'Find the user by entering (part of) the email address. Users who are already linked are not included in the results.',

    'allowed_email_domains' => 'Permitted user email domains',
    'allowed_email_domains_help' => 'If no domains are added here, no restrictions are applied.',
    'allowed_email_domains_add' => 'Add domain',
    'avatar' => 'Avatar',
    'poster' => 'Poster',
    'slug' => 'URL segment',
    'allowed_ips' => 'Permitted IP addresses',
    'review_at_default_in_months' => 'Default period for periodic review (in months)',
    'public_website_content' => 'Public website text',

    'help_slug' => 'Determines the web address of the portal; use only lowercase letters and hyphens.',
    'help_allowed_ips' => 'One IP address, range or CIDR notation per line; does not apply to the login page itself.',
    'help_review_at_default_in_months' => 'Applied as soon as a version is established and the review date is still empty.',
    'help_responsible_legal_entity' => 'The legal entity this organisation falls under.',
    'help_public_website_content' => 'Introductory text above the registers on the public website.',
    'help_poster' => 'Image at the top of the public website; a landscape image works best.',
    'entity_number_prefix' => 'Prefix',
    'register_entity_number_prefix' => 'Processing activity prefix',
    'databreach_entity_number_prefix' => 'Personal data breach prefix',
    'entity_number_prefix_edit' => 'Edit prefix',
    'entity_number_unique_validation_message' => 'This prefix is already (or has previously been) in use, possibly by another organisation: it is no longer available.',

    'public_from_hint_icon_text' => 'Please note: if you leave this field empty, the processing activity will never be published to the public website.',
    'section_ap' => 'Details for data breach notifications',
    'coc_number' => 'Chamber of Commerce number',
    'fg_registration_number' => 'DPO registration number',
    'sector' => 'Sector',
    'help_coc_number' => 'Carried over into the notification to the Dutch Data Protection Authority.',
    'help_fg_registration_number' => 'The number under which the data protection officer is registered with the AP.',
    'help_sector' => 'The sector the organisation operates in; the AP asks for this when notifying.',
];
