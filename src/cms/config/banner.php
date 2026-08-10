<?php

declare(strict_types=1);

// Operator-configured banner shown at the top of every panel page, including the
// login page. Intended for environment-level notices such as "this is a demo
// environment". Leave the message unset to disable the banner entirely.
return [
    'message' => env('APP_BANNER_MESSAGE'),
    'level' => env('APP_BANNER_LEVEL', 'warning'),
];
