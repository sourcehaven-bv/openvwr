<?php

declare(strict_types=1);

namespace Tests\Unit\PHPStan\Rules;

use App\PHPStan\Rules\TenantAwareQueryRule;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

use function sprintf;

/** @extends RuleTestCase<TenantAwareQueryRule> */
class TenantAwareQueryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TenantAwareQueryRule(
            self::getContainer()->getByType(ReflectionProvider::class),
        );
    }

    public function testFilamentQueriesMustUseTenantScopedEntryPoint(): void
    {
        $message = 'Start queries for tenant-aware model App\\Models\\Tag '
            . 'with App\\Models\\Tag::tenantQuery().';

        $this->analyse([
            sprintf('%s/../../../Fixtures/PHPStan/tenant-aware-query.php', __DIR__),
            sprintf('%s/../../../Fixtures/PHPStan/non-filament-tenant-aware-query.php', __DIR__),
        ], [
            [$message, 9],
            [$message, 10],
            [$message, 11],
        ]);
    }
}
