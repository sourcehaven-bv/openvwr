<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components\Upload;

use App\Components\Uuid\Uuid;
use App\Filament\Forms\Components\Upload\AttachmentFileField;
use App\Models\Organisation;
use App\Rules\ExtensionMimeType;
use App\Rules\Virusscanner as VirusscannerRule;
use App\Services\AuthenticationService;
use App\Services\Virusscanner\Virusscanner as VirusscannerService;
use Closure;
use Mockery;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\Mime\MimeTypesInterface;
use Tests\TestCase;

use function app;
use function config;
use function expect;
use function it;
use function uses;

uses(TestCase::class);

function makeAttachmentField(): AttachmentFileField
{
    config()->set('media-library.permitted_file_types.attachment', ['image/jpeg', 'image/png']);
    config()->set('media-library.permitted_file_extensions.attachment', [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
    ]);

    $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');

    $org = Mockery::mock(Organisation::class);
    $org->shouldReceive('getAttribute')->with('id')->andReturn($uuid);

    $authMock = Mockery::mock(AuthenticationService::class);
    $authMock->shouldReceive('organisation')->andReturn($org);
    app()->instance(AuthenticationService::class, $authMock);

    return AttachmentFileField::make('attachments');
}

function getAttachmentExtensionMimeTypeRulesClosure(): Closure
{
    $field = makeAttachmentField();

    $class = new ReflectionObject($field);
    while (!$class->hasProperty('rules')) {
        $class = $class->getParentClass();
        if ($class === false) {
            throw new RuntimeException('rules property not found');
        }
    }

    $property = $class->getProperty('rules');
    $rules = $property->getValue($field);

    foreach ($rules as [$rule]) {
        if (!($rule instanceof Closure)) {
            continue;
        }

        $params = (new ReflectionFunction($rule))->getParameters();
        if (isset($params[1]) && (string) $params[1]->getType() === MimeTypesInterface::class) {
            return $rule;
        }
    }

    throw new RuntimeException('ExtensionMimeType factory closure not found in rules');
}

function getAttachmentVirusscannerRulesClosure(): Closure
{
    $field = makeAttachmentField();

    $class = new ReflectionObject($field);
    while (!$class->hasProperty('rules')) {
        $class = $class->getParentClass();
        if ($class === false) {
            throw new RuntimeException('rules property not found');
        }
    }

    $property = $class->getProperty('rules');
    $rules = $property->getValue($field);

    foreach ($rules as [$rule]) {
        if (!($rule instanceof Closure)) {
            continue;
        }

        $params = (new ReflectionFunction($rule))->getParameters();
        if (isset($params[1]) && (string) $params[1]->getType() === VirusscannerService::class) {
            return $rule;
        }
    }

    throw new RuntimeException('Virusscanner factory closure not found in rules');
}

it('ExtensionMimeType rules closure creates an ExtensionMimeType instance', function (): void {
    $closure = getAttachmentExtensionMimeTypeRulesClosure();

    $logger = Mockery::mock(LoggerInterface::class);
    $mimeTypes = Mockery::mock(MimeTypesInterface::class);

    $rule = $closure($logger, $mimeTypes);

    expect($rule)->toBeInstanceOf(ExtensionMimeType::class);
});

it('Virusscanner rules closure creates a Virusscanner instance', function (): void {
    $closure = getAttachmentVirusscannerRulesClosure();

    $logger = Mockery::mock(LoggerInterface::class);
    $virusscanner = Mockery::mock(VirusscannerService::class);

    $rule = $closure($logger, $virusscanner);

    expect($rule)->toBeInstanceOf(VirusscannerRule::class);
});

it('inverts permitted_file_extensions into a flat extension to mime-type map', function (): void {
    $field = makeAttachmentField();

    expect($field->getMimeTypeMap())->toBe([
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ]);
});
