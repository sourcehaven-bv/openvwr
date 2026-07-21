<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components\Upload;

use App\Filament\Forms\Components\Upload\PosterFileField;
use App\Rules\ExtensionMimeType;
use Closure;
use Mockery;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\Mime\MimeTypesInterface;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

function getPosterFieldRulesClosure(): Closure
{
    $field = PosterFileField::make('poster');

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
        if (isset($params[0]) && (string) $params[0]->getType() === LoggerInterface::class) {
            return $rule;
        }
    }

    throw new RuntimeException('ExtensionMimeType factory closure not found in rules');
}

it('rules closure creates an ExtensionMimeType instance', function (): void {
    $closure = getPosterFieldRulesClosure();

    $logger = Mockery::mock(LoggerInterface::class);
    $mimeTypes = Mockery::mock(MimeTypesInterface::class);

    $rule = $closure($logger, $mimeTypes);

    expect($rule)->toBeInstanceOf(ExtensionMimeType::class);
});
