<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Config;
use Tests\TestCase;

use function array_keys;
use function it;
use function sprintf;
use function uses;

uses(TestCase::class);

it('keeps permitted media mime types and file extensions in sync', function (): void {
    $permittedFileTypes = Config::array('media-library.permitted_file_types');
    $permittedFileExtensions = Config::array('media-library.permitted_file_extensions');

    $this->assertEqualsCanonicalizing(
        array_keys($permittedFileTypes),
        array_keys($permittedFileExtensions),
        'Every media collection with permitted file types must have permitted file extensions, and vice versa.',
    );

    foreach ($permittedFileTypes as $collection => $mimeTypes) {
        $this->assertIsArray($mimeTypes, sprintf('Expected permitted_file_types.%s to be an array.', $collection));
        foreach ($mimeTypes as $mimeType) {
            $this->assertIsString(
                $mimeType,
                sprintf('Expected permitted_file_types.%s to contain only MIME type strings.', $collection),
            );
        }

        $extensionsByMimeType = $permittedFileExtensions[$collection] ?? [];
        $this->assertIsArray(
            $extensionsByMimeType,
            sprintf('Expected permitted_file_extensions.%s to be an array.', $collection),
        );

        $this->assertEqualsCanonicalizing(
            $mimeTypes,
            array_keys($extensionsByMimeType),
            sprintf(
                'permitted_file_extensions.%s must define extensions for exactly the MIME types in permitted_file_types.%s.',
                $collection,
                $collection,
            ),
        );

        foreach ($extensionsByMimeType as $mimeType => $extensions) {
            $this->assertIsArray(
                $extensions,
                sprintf('Expected permitted_file_extensions.%s.%s to be an array.', $collection, $mimeType),
            );
            $this->assertNotEmpty(
                $extensions,
                sprintf('Expected permitted_file_extensions.%s.%s to define at least one extension.', $collection, $mimeType),
            );
            foreach ($extensions as $extension) {
                $this->assertIsString(
                    $extension,
                    sprintf(
                        'Expected permitted_file_extensions.%s.%s to contain only extension strings.',
                        $collection,
                        $mimeType,
                    ),
                );
            }
        }
    }
});
