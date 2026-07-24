<?php

declare(strict_types=1);

// Deployment version, overwritten with build numbers by scripts/create-release.sh
// when the release archive is built. Keep these as literal values (no env()) so
// they survive `php artisan config:cache`.
return [
    'label' => 'dev',
    'sha' => null,
];
