<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Answers "who is acting, and where" by delegating to the configured
 * AuthenticationStrategy.
 *
 * The public surface here is unchanged from when this class held the logic
 * directly — the Authentication facade and its ~50 callers are deliberately
 * untouched by the strategy extraction.
 */
class AuthenticationService
{
    public function __construct(
        private readonly AuthenticationStrategy $strategy,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function organisation(): Organisation
    {
        return $this->strategy->organisation();
    }

    public function principal(): Principal
    {
        return $this->strategy->principal();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function user(): User
    {
        return $this->strategy->user();
    }
}
