<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use RuntimeException;

use function implode;
use function in_array;
use function sprintf;

/**
 * Resolves the configured auth driver to a strategy, once, at boot.
 *
 * Resolution fails loudly: an unknown driver is a startup error rather than a
 * silent fallback, because "quietly authenticated some other way" is exactly the
 * failure mode worth making impossible.
 */
final class AuthenticationStrategyFactory
{
    public const DRIVER_BUILTIN = 'builtin';
    public const DRIVER_DEV = 'dev';

    /** Environments in which the credential-free dev driver may run. */
    private const DEV_ENVIRONMENTS = ['local', 'testing'];

    /**
     * @throws RuntimeException on an unknown driver, or on `dev` outside local/testing
     */
    public static function make(string $driver, string $environment): AuthenticationStrategy
    {
        return match ($driver) {
            self::DRIVER_BUILTIN => new BuiltinAuthenticationStrategy(),
            self::DRIVER_DEV => self::makeDev($environment),
            default => throw new RuntimeException(sprintf(
                'Unknown auth driver "%s". Valid drivers: %s.',
                $driver,
                implode(', ', [self::DRIVER_BUILTIN, self::DRIVER_DEV]),
            )),
        };
    }

    /** Whether the dev driver is permitted to run in this environment. */
    public static function devAllowedIn(string $environment): bool
    {
        return in_array($environment, self::DEV_ENVIRONMENTS, true);
    }

    /**
     * The dev driver logs anyone in without credentials, so reaching production
     * with it enabled would be a full authentication bypass. Refuse to boot
     * rather than serve a single request that way.
     *
     * @throws RuntimeException
     */
    private static function makeDev(string $environment): AuthenticationStrategy
    {
        if (!self::devAllowedIn($environment)) {
            throw new RuntimeException(sprintf(
                'The "%s" auth driver is a credential-free login and cannot run in the "%s" environment. '
                . 'It is permitted only in: %s.',
                self::DRIVER_DEV,
                $environment,
                implode(', ', self::DEV_ENVIRONMENTS),
            ));
        }

        return new DevAuthenticationStrategy();
    }
}
