<?php

declare(strict_types=1);

use App\Config\Config;

it('is an array', function (): void {
    $configKey = fake()->word();
    $configValue = [fake()->word() => fake()->boolean()];

    config()->set($configKey, $configValue);

    $this->assertSame($configValue, Config::array($configKey));
});

it('is not an array', function (): void {
    $configKey = fake()->word();
    $configValue = fake()->boolean();

    config()->set($configKey, $configValue);

    $this->expectException(InvalidArgumentException::class);
    $this->assertSame($configValue, Config::array($configKey));
});

it('is a boolean', function (): void {
    $configKey = fake()->word();
    $configValue = fake()->boolean();

    config()->set($configKey, $configValue);

    $this->assertSame($configValue, Config::boolean($configKey));
});

it('is not a boolean', function (): void {
    $configKey = fake()->word();

    config()->set($configKey, []);

    $this->expectException(InvalidArgumentException::class);
    Config::boolean($configKey);
});

it('is an integer', function ($configValue, $expectedValue): void {
    $configKey = fake()->word();
    config()->set($configKey, $configValue);

    $this->assertSame($expectedValue, Config::integer($configKey));
})->with('integers');

dataset('integers', [
    'positive integer' => [15, 15],
    'zero integer' => [0, 0],
    'negative integer' => [-5, -5],
    'positive string' => ['123', 123],
    'zero string' => ['0', 0],
    'negative string' => ['-5', -5],
]);

it('is not an integer', function (): void {
    $configKey = fake()->word();

    config()->set($configKey, []);

    $this->expectException(InvalidArgumentException::class);
    Config::integer($configKey);
});

it('is a string', function (): void {
    $configKey = fake()->word();
    $configValue = fake()->word();

    config()->set($configKey, $configValue);

    $this->assertSame($configValue, Config::string($configKey));
});

it('is not a string', function (): void {
    $configKey = fake()->word();

    config()->set($configKey, []);

    $this->expectException(InvalidArgumentException::class);
    Config::string($configKey);
});

it('is a string or null', function (): void {
    $configKey = fake()->word();
    $configValue = fake()->optional()->word();

    config()->set($configKey, $configValue);

    $this->assertSame($configValue, Config::stringOrNull($configKey));
});

it('is not a string or null', function (): void {
    $configKey = fake()->word();

    config()->set($configKey, []);

    $this->expectException(InvalidArgumentException::class);
    Config::stringOrNull($configKey);
});

it('checks whether a config key exists', function (): void {
    $configKey = fake()->word();

    config()->set($configKey, fake()->word());

    $this->assertTrue(Config::has($configKey));
    $this->assertFalse(Config::has($configKey . '_missing'));
});
