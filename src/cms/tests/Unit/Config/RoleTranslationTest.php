<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Enums\Authorization\Role;
use Tests\TestCase;

use function __;
use function it;
use function sprintf;
use function uses;

uses(TestCase::class);

it('has a label and a description for every role', function (): void {
    foreach (Role::cases() as $role) {
        $labelKey = sprintf('role.%s', $role->value);
        $descriptionKey = sprintf('role.descriptions.%s', $role->value);

        $this->assertNotSame(
            $labelKey,
            __($labelKey),
            sprintf('Missing translation for %s.', $labelKey),
        );

        $this->assertNotSame(
            $descriptionKey,
            __($descriptionKey),
            sprintf('Missing translation for %s.', $descriptionKey),
        );
    }
});
