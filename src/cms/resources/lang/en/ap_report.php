<?php

declare(strict_types=1);

return [
    'title' => 'Preparation for notifying the Dutch Data Protection Authority',
    'action_label' => 'Prepare AP notification form',
    'action_pdf_label' => 'Download as PDF',

    'intro' => 'This overview follows the chapter structure and question numbers of the online data breach'
        . ' notification form of the Autoriteit Persoonsgegevens, so the form can be filled in from top to'
        . ' bottom. The AP only accepts notifications through its online form; this document supports'
        . ' filling that form in and is not itself a notification.',
    'portal_hint' => 'Notification form: https://datalekken.autoriteitpersoonsgegevens.nl',

    'summary_title' => 'Before you start',
    'summary_missing' => 'Still to be collected (:count)',
    'summary_missing_empty' => 'Every question this register can answer has been filled in.',
    'summary_confirm' => 'Suggestions from linked content to verify (:count)',
    'summary_confirm_explanation' => 'These answers are derived from the processing records this data breach'
        . ' is linked to. They describe that processing and not necessarily this breach: check for each answer'
        . ' whether it was actually part of the leak before entering it in the notification form.',

    'hint_prefix' => 'The linked processing record mentions',
    'hint_explanation' => 'Record on the data breach what was actually leaked.',

    'source_recorded' => 'From the data breach register',
    'source_derived' => 'Derived from linked content - verify',
    'source_missing' => 'Not in the register - fill in yourself',
    'origin_data_protection_officials' => 'Data protection officers of the organisation',
    'origin_prefix' => 'Source',
    'not_recorded' => 'To be filled in',

    'chapter' => [
        'introduction' => 'Introduction',
        'international' => 'International aspects',
        'controller' => 'The controller',
        'timeline' => 'Timeline',
        'breach' => 'Details of the breach',
        'personal_data' => 'Which personal data',
        'affected_people' => 'Affected people',
        'prior_measures' => 'Measures taken beforehand',
        'consequences' => 'Consequences',
        'follow_up' => 'Follow-up actions',
    ],

    'question' => [
        'notification_kind' => 'What kind of notification do you want to make?',
        'legal_basis' => 'Under which statutory provision are you making this notification?',
        'other_supervisors' => 'Have you reported the breach to supervisors for other notification duties?',
        'cross_border' => 'Does the breach affect people in more than one country?',
        'reported_other_dpas' => 'Has your organisation reported the breach to other data protection authorities?',
        'organisation_name' => 'Name of the company or organisation',
        'responsible' => 'Controller(s)',
        'address' => 'Address, postal code and city',
        'fg_registration_number' => 'Registration number of the data protection officer',
        'coc_number' => 'Chamber of Commerce number',
        'sector' => 'In which sector does the organisation operate?',
        'reporter' => 'Who is reporting the breach? (name, position, email address, telephone number)',
        'contact_person' => 'Contact person for the AP, if different from the reporter',
        'other_organisations' => 'Were other organisations involved in the breach?',
        'started_at' => '(Possible) start date of the breach',
        'ended_at' => '(Possible) end date of the breach',
        'discovered_at' => 'When was the incident discovered?',
        'how_discovered' => 'Briefly describe how you discovered the breach',
        'late_notification_reason' => 'If reported later than 72 hours: why?',
        'nature_of_breach' => 'Nature of the breach (confidentiality, integrity, availability)',
        'nature_of_incident' => 'Nature of the incident',
        'summary' => 'Description of the incident',
        'attachments' => 'Supporting documentation',
        'personal_data_categories' => 'Personal data in general',
        'special_categories' => 'Special categories of personal data',
        'record_count' => 'How many data records were affected?',
        'affected_groups' => 'Which group(s) of data subjects were affected?',
        'affected_description' => 'Further description of the group(s) of data subjects',
        'affected_count' => 'Number of data subjects (exact, or minimum and maximum)',
        'encrypted_beforehand' => 'Were the personal data encrypted, hashed or otherwise made inaccessible beforehand?',
        'pseudonymisation_from_processing' => 'Pseudonymisation according to the linked processing record(s)',
        'consequences_controller' => '(Possible) consequences for the controller and the personal data',
        'consequences_data_subjects' => '(Possible) consequences for the data subject(s)',
        'risk_severity' => 'Severity assessment: negligible, limited, significant or very high',
        'estimated_risk' => 'Explanation of the risk assessment',
        'reported_to_involved' => 'Have you reported the breach to the data subject(s)?',
        'reported_to_involved_communication' => 'How were the data subjects informed?',
        'reported_to_involved_count' => 'To how many people have you reported the breach?',
        'measures' => 'What measures have you taken?',
    ],
];
