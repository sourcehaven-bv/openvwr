<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Forms\FormHelper;
use Filament\Forms\Get;
use RuntimeException;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

use function expect;
use function fake;
use function it;

it('returns get correct state on fieldValuesContainValue', function (array $fieldValues, string $value, bool $expectedResult): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn($fieldValues)
        ->getMock();

    $isFieldEnabled = FormHelper::fieldValuesContainValue($fieldName, $value);

    expect($isFieldEnabled($getterMock))
        ->toBe($expectedResult);
})->with([
    [['foo', 'bar'], 'foo', true],
    [['foo', 'bar'], 'bar', true],
    [['foo', 'bar'], 'baz`', false],
]);

it('returns false if a non-array value is passed to get the fieldValuesContainValue', function (): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn(false)
        ->getMock();

    $isFieldEnabled = FormHelper::fieldValuesContainValue($fieldName, fake()->word());

    expect($isFieldEnabled($getterMock))
        ->toBeFalse();
});

it('returns get correct enabled-status of a field', function (bool $status, bool $expectedResult): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn($status)
        ->getMock();

    $isFieldEnabled = FormHelper::isFieldEnabled($fieldName);

    expect($isFieldEnabled($getterMock))
        ->toBe($expectedResult);
})->with([
    [true, true],
    [false, false],
]);

it('returns get correct disabled-status of a field', function (bool $status, bool $expectedResult): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn($status)
        ->getMock();

    $isFieldEnabled = FormHelper::isFieldDisabled($fieldName);

    expect($isFieldEnabled($getterMock))
        ->toBe($expectedResult);
})->with([
    [true, false],
    [false, true],
]);

it('returns false if a non-boolean value is passed to get the enabled-status', function (): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn([])
        ->getMock();

    $isFieldEnabled = FormHelper::isFieldEnabled($fieldName);

    expect($isFieldEnabled($getterMock))
        ->toBeFalse();
});

it('returns false if a non-boolean value is passed to get the disabled-status', function (): void {
    $fieldName = fake()->word();

    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with($fieldName)
        ->andReturn([])
        ->getMock();

    $isFieldEnabled = FormHelper::isFieldDisabled($fieldName);

    expect($isFieldEnabled($getterMock))
        ->toBeFalse();
});

it('returns the correct auth-fields', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->asFilamentUser($user);

    $addAuthFields = FormHelper::addAuthFields();
    $authFields = $addAuthFields([]);

    expect($authFields['organisation_id'])
        ->toBe($organisation->id->toString())
        ->and($authFields['user_id'])
        ->toBe($user->id);
});

it('returns true only when every field matches its expected value', function (bool $fieldB, bool $expectedResult): void {
    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('a')
        ->andReturn(true)
        ->shouldReceive('__invoke')
        ->with('b')
        ->andReturn($fieldB)
        ->getMock();

    $matches = FormHelper::fieldValueEquals(['a' => true, 'b' => true]);

    expect($matches($getterMock))
        ->toBe($expectedResult);
})->with([
    [true, true],
    [false, false],
]);

it('returns correct value if any field is enabled', function (bool $fieldA, bool $fieldB, bool $expectedResult): void {
    $getterMock = $this->mock(Get::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('a')
        ->andReturn($fieldA)
        ->shouldReceive('__invoke')
        ->times($fieldA ? 0 : 1)
        ->with('b')
        ->andReturn($fieldB)
        ->getMock();

    $isAnyFieldEnabled = FormHelper::isAnyFieldEnabled(['a', 'b']);

    expect($isAnyFieldEnabled($getterMock))
        ->toBe($expectedResult);
})->with([
    [true, true, true],
    [true, false, true],
    [false, true, true],
    [false, false, false],
]);

it('renders helper text with a link to the target register', function (): void {
    $helperText = FormHelper::helperTextWithLink(
        'Koppel de algoritmes.',
        'Naar het Algoritmeregister',
        static fn (): string => 'https://example.test/nipg/algorithm-records',
    );

    expect($helperText()->toHtml())
        ->toContain('Koppel de algoritmes.')
        ->toContain('href="https://example.test/nipg/algorithm-records"')
        ->toContain('Naar het Algoritmeregister')
        ->toContain('target="_blank"');
});

it('falls back to plain helper text when the url cannot be resolved', function (): void {
    $helperText = FormHelper::helperTextWithLink(
        'Koppel de algoritmes.',
        'Naar het Algoritmeregister',
        static function (): string {
            throw new RuntimeException('no tenant in this context');
        },
    );

    expect($helperText()->toHtml())
        ->toBe('Koppel de algoritmes.')
        ->not->toContain('<a href');
});
