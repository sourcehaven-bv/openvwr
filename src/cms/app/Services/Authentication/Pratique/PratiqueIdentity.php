<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use App\Models\Organisation;
use App\Models\User;

/**
 * The local rows a verified assertion resolves to: who is acting, and where.
 */
final readonly class PratiqueIdentity
{
    public function __construct(
        public User $user,
        public Organisation $organisation,
    ) {
    }
}
