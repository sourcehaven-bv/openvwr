<?php

declare(strict_types=1);

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Driver (strategy)
    |--------------------------------------------------------------------------
    |
    | Selects how identity is established:
    |
    |   builtin  passwordless magic link + TOTP on the session guard (default)
    |   dev      pick a user from a dropdown, no credentials — local/testing ONLY
    |
    | An unknown value is a startup error, and "dev" refuses to boot outside the
    | local and testing environments: it is a deliberate authentication bypass.
    |
    */

    'driver' => env('AUTH_DRIVER', 'builtin'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */


    // passwordless
    'passwordless' => [
        'token_expiry_minutes' => env('PASSWORDLESS_TOKEN_EXPIRY_MINUTES', 5),
        'throttle' => [
            'window' => env('PASSWORDLESS_THROTTLE_WINDOW_SECONDS', 60 * 5),
            'max_attempts' => env('PASSWORDLESS_THROTTLE_MAX_ATTEMPTS', 5),
        ],
    ],

    'one_time_password' => [
        /*
         * Options: "timed", "fake"
         */
        'driver' => env('ONE_TIME_PASSWORD_DRIVER', 'timed'),

        'validation_rate_limit' => [
            'max_attempts' => 3,
            'decay_in_seconds' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pratique (identity proxy)
    |--------------------------------------------------------------------------
    |
    | Settings for the "pratique" auth driver, where a proxy in front of the app
    | authenticates the user and forwards a signed assertion. Only used when
    | auth.driver is "pratique".
    |
    | The audience MUST match the proxy's upstream.audience exactly. A mismatch
    | is the single most common cause of an endless 403 loop, and a lenient
    | check here would be a confused-deputy hole: an assertion minted for
    | another upstream would be accepted as ours.
    |
    | leeway_seconds allows for small clock drift between the proxy host and
    | this one when checking exp/nbf. Keep it small — the assertion lives ~9
    | minutes — and keep both hosts on NTP regardless.
    |
    */

    'pratique' => [
        'issuer' => env('PRATIQUE_ISSUER'),
        'audience' => env('PRATIQUE_AUDIENCE'),
        'jwks_url' => env('PRATIQUE_JWKS_URL'),
        'jwks_cache_seconds' => env('PRATIQUE_JWKS_CACHE_SECONDS', 300),
        'leeway_seconds' => env('PRATIQUE_LEEWAY_SECONDS', 60),
    ],
];
