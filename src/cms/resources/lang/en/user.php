<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Enums\Snapshot\MandateholderNotifyBatch;
use App\Enums\Snapshot\MandateholderNotifyDirectly;

return [
    'model_singular' => 'User',
    'model_plural' => 'Users',
    'table_empty_heading' => 'No users',
    'role' => 'Role',
    'global_roles' => 'Global roles',

    'organisation_roles' => 'Organisation roles',
    'organisation_roles_add' => 'Add organisation role',
    'organisation_attach' => 'Link organisations',
    'organisation_attach_description' => 'Find the organisation by entering (part of) the name. Organisations that are already linked are not included in the results.',
    'organisation_role_attach' => 'Add user to organisation',
    'organisation_role_attach_exists' => 'This user has already been added to this organisation',
    'organisation_role_attach_description' => 'You are about to remove this user from this organisation: are you sure?',
    'organisation_role_detach' => 'Remove user',
    'organisation_role_detach_alternate_primary_contact' => 'Alternative Primary Contact',
    'organisation_role_detach_description' => 'You are about to remove this user from this organisation: are you sure?',

    'name' => 'Name',
    'password' => 'Password',
    'email' => 'Email',
    'email_domain_not_allowed' => 'Email domain not permitted, permitted domains are: :allowedEmailDomains',

    'one_time_password' => [
        'code' => 'Code',
        'disable' => 'Reset 2FA',
        'disabled' => '2FA has been reset',
        'heading' => 'Two-factor authentication',
        'description' => 'Confirm access to your account by entering the authentication code provided by your authentication application. If you do not have authentication codes, contact the privacy officer or administration.',
        'back_to_login_link' => 'Back to Login',
    ],

    'profile' => [
        'my_profile' => 'My profile',

        'personal_info' => [
            'heading' => 'Personal information',
            'subheading' => 'Manage your personal information',
            'submit' => 'Save',
            'notify' => 'Personal information updated!',
        ],

        'one_time_password' => [
            'title' => 'Two-factor authentication',
            'description' => 'Manage two-factor authentication for your account',
            'code' => 'Code',
            'must_enable' => 'Two-factor authentication is mandatory',
            'actions' => [
                'enable' => 'Enable',
                'disable' => 'Reset',
                'confirm_finish' => 'Confirm',
                'cancel_setup' => 'Cancel',
            ],
            'setup_key' => 'Key: ',
            'enabled' => [
                'title' => 'You have enabled two-factor authentication!',
                'description' => 'Two-factor authentication is now enabled. This improves the security of your account.',
                'notify' => 'Two-factor authentication enabled',
            ],
            'disabling' => [
                'notify' => 'Two-factor authentication reset',
            ],
            'finish_enabling' => [
                'title' => 'Finish enabling two-factor authentication.',
                'description' => 'To finish enabling two-factor authentication, scan the following QR code using your phone\'s authentication application or enter the key manually.',
            ],
            'not_enabled' => [
                'title' => 'You have not enabled two-factor authentication.',
                'description' => 'When two-factor authentication is enabled, you are asked for a secure, random token during authentication. You can retrieve this token from, for example, the Google Authenticator app on your phone.',
            ],
            'regenerate_codes' => [
                'action' => 'Regenerate codes',
                'notify' => 'The codes have been regenerated and saved',
            ],
            'confirmation' => [
                'success_notification' => 'Code verified. Two-factor authentication enabled.',
                'invalid_code' => 'The code you entered is invalid.',
                'too_many_requests' => 'Too many login attempts, please try again in :seconds seconds.',
            ],
        ],

        'settings' => [
            'heading' => 'Settings',
            'subheading' => 'Manage your personal settings',
            'submit' => 'Save',
            'notify' => 'Settings updated!',

            'mandateholder' => 'Mandate holder',
            'mandateholder_notify_batch' => 'Summary',
            'mandateholder_notify_batch_options' => [
                MandateholderNotifyBatch::NONE->value => 'None',
                MandateholderNotifyBatch::WEEKLY->value => 'Weekly',
            ],

            'mandateholder_notify_directly' => 'For every invitation',
            'mandateholder_notify_directly_options' => [
                MandateholderNotifyDirectly::NONE->value => 'None',
                MandateholderNotifyDirectly::BATCH->value => 'Summary',
                MandateholderNotifyDirectly::SINGLE->value => 'Separate message',
            ],

            'layout' => 'Layout',
            'register_layout' => 'Register layout',
            'register_layout_options' => [
                RegisterLayout::STEPS->value => 'Step by step with navigation on the right',
                RegisterLayout::ONE_PAGE->value => 'All details on one page',
            ],
            'register_layout_switch_to_one_page' => 'All on one page',
            'register_layout_switch_to_steps' => 'Step by step',
        ],
    ],
];
