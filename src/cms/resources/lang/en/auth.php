<?php

declare(strict_types=1);

return [
    'login_heading' => 'Log in',
    'login_subheading' => 'Enter your email address. You will receive an email with a link that logs you straight in; no password needed.',
    'login_sent' => 'Login information sent',

    // Mailable already prefixes the subject with "[OpenVWR]: ", so the
    // application name is not repeated here. Also without the word "link":
    // mail filters weigh that, and a login email that lands in the spam
    // folder blocks the only way in.
    'passwordless_login_subject' => 'Your login email',
    'passwordless_login_greeting' => 'Hello :userName,',
    'passwordless_login_text' => 'You requested a login email for :appName. Use the button below to log in.',
    'passwordless_login_button_text' => 'Log in to :appName',
    'passwordless_login_expiry' => 'This link is valid until :validUntil and can be used once.',
    'passwordless_login_ignore' => 'Did you not request a login email? Then no action is needed: without using the button above, nobody gains access to your account.',
    'passwordless_login_fallback' => 'Button not working? Copy this link into your browser:',
    'email_sent' => 'Email sent',

    'login_link_expired' => 'This login link has expired. Request a new login email to log in.',
    'login_link_invalid' => 'This login link is not valid. Request a new login email to log in.',
    'login_no_organisation' => 'Your account is not linked to an organisation yet. Please contact your administrator.',

    'snapshot_sign_subject' => 'Login link',
    'snapshot_sign_login_text' => 'Hello :userName, click the button below to gain access to OpenVWR',
    'snapshot_sign_login_button_text' => 'Go to OpenVWR',
    'snapshot_sign_login_footer' => 'This link is valid until: :validUntil',

    'confirm_login' => 'Log in',
    'confirm_message' => 'Press the button below to gain access to OpenVWR',

    'snapshot_sign_confirm_message' => 'Hello :userName, click below to start the authentication process',
    'snapshot_sign_confirm_login' => 'Send login email',
    'snapshot_sign_confirm_help_text' => 'The login email serves to establish whether you currently actually have access to the email address known to us.',
];
