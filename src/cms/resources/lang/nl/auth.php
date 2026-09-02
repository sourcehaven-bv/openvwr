<?php

declare(strict_types=1);

return [
    'login_heading' => 'Inloggen',
    'login_subheading' => 'Vul uw e-mailadres in. U ontvangt een e-mail met een link waarmee u direct inlogt; een wachtwoord heeft u niet nodig.',
    'login_sent' => 'Login-informatie verzonden',

    // Zonder het woord "link": mailfilters wegen dat mee, en een loginmail
    // die in de spambox belandt blokkeert de enige manier om binnen te komen.
    // De afzender noemt de applicatie al, dus het onderwerp doet dat niet.
    'passwordless_login_subject' => 'Uw inlogmail',
    'passwordless_login_greeting' => 'Hallo :userName,',
    'passwordless_login_text' => 'U heeft een inlogmail aangevraagd voor :appName. Klik op de knop hieronder om in te loggen.',
    'passwordless_login_button_text' => 'Inloggen bij :appName',
    'passwordless_login_expiry' => 'Deze link is geldig tot :validUntil en kan één keer worden gebruikt.',
    'passwordless_login_ignore' => 'Heeft u zelf geen inlogmail aangevraagd? Dan hoeft u niets te doen: zonder de knop hierboven te gebruiken krijgt niemand toegang tot uw account.',
    'passwordless_login_fallback' => 'Werkt de knop niet? Kopieer dan deze link naar uw browser:',
    'email_sent' => 'E-mail verzonden',

    'login_link_expired' => 'Deze loginlink is verlopen. Vraag een nieuwe login-e-mail aan om in te loggen.',
    'login_link_invalid' => 'Deze loginlink is niet geldig. Vraag een nieuwe login-e-mail aan om in te loggen.',
    'login_no_organisation' => 'Uw account is nog niet aan een organisatie gekoppeld. Neem contact op met uw beheerder.',

    'snapshot_sign_subject' => 'Login link',
    'snapshot_sign_login_text' => 'Hallo :userName, klik op de onderstaande knop om toegang te krijgen tot OpenVWR',
    'snapshot_sign_login_button_text' => 'Naar OpenVWR',
    'snapshot_sign_login_footer' => 'Deze link is geldig t/m: :validUntil',

    'confirm_login' => 'Inloggen',
    'confirm_message' => 'Druk op de onderstaande knop om toegang te krijgen tot OpenVWR',

    'snapshot_sign_confirm_message' => 'Hallo :userName, klik hieronder om het authenticatieproces te starten',
    'snapshot_sign_confirm_login' => 'Stuur login email',
    'snapshot_sign_confirm_help_text' => 'De login email is om te bepalen of u op dit moment werkelijk toegang heeft tot het email adres dat bij ons bekend is.',

    // Ontwikkelomgeving: inloggen zonder wachtwoord of tweede factor.
    'dev_login_heading' => 'Inloggen (ontwikkelomgeving)',
    'dev_login_user' => 'Gebruiker',
];
