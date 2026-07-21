<?php

declare(strict_types=1);

use App\Rules\ExtensionMimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Psr\Log\NullLogger;
use Symfony\Component\Mime\MimeTypesInterface;

it('fails if no uploadedfile provided', function (): void {
    $validated = true;

    $extensionMimeType = $this->app->get(ExtensionMimeType::class);

    $this->expectException(InvalidArgumentException::class);
    $extensionMimeType->validate(fake()->word(), fake()->word(), function () use (&$validated): void {
        $validated = false;
    });
});

it('validates with a real file', function (): void {
    $validated = true;
    $fileExtension = fake()->fileExtension();

    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')
        ->once()
        ->with('text/plain')
        ->andReturn([
            $fileExtension,
        ]);

    $fileName = sprintf('/tmp/%s.%s', fake()->uuid(), $fileExtension);
    File::put($fileName, fake()->sentence());
    $uploadedFile = new UploadedFile($fileName, $fileName);

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeTrue();
});

it('fails when extension and mime-type do not match', function (): void {
    $validated = true;

    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')
        ->once()
        ->with('text/plain')
        ->andReturn([
            fake()->unique()->fileExtension(),
            fake()->unique()->fileExtension(),
        ]);

    $fileName = sprintf('/tmp/%s.%s', fake()->uuid(), fake()->unique()->fileExtension());
    File::put($fileName, fake()->sentence());
    $uploadedFile = new UploadedFile($fileName, $fileName);

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeFalse();
});

it('passes when extension is in the explicit allowlist for detected mime-type', function (): void {
    $validated = true;

    $fileName = sprintf('/tmp/%s.txt', fake()->uuid());
    File::put($fileName, fake()->sentence());
    $uploadedFile = new UploadedFile($fileName, $fileName);

    $allowedExtensions = ['text/plain' => ['txt']];
    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')->never();

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes, $allowedExtensions);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeTrue();
});

it('blocks a dangerous extension even though content is text/plain', function (): void {
    $validated = true;

    $fileName = sprintf('/tmp/%s.bat', fake()->uuid());
    File::put($fileName, '@echo off');
    $uploadedFile = new UploadedFile($fileName, $fileName);

    $allowedExtensions = ['text/plain' => ['txt']];
    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')->never();

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes, $allowedExtensions);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeFalse();
});

it('fails when detected mime-type has no entry in the allowlist', function (): void {
    $validated = true;

    $fileName = sprintf('/tmp/%s.txt', fake()->uuid());
    File::put($fileName, fake()->sentence());
    $uploadedFile = new UploadedFile($fileName, $fileName);

    // allowlist does not contain text/plain at all
    $allowedExtensions = ['image/png' => ['png']];
    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')->never();

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes, $allowedExtensions);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeFalse();
});

it('fails when mime-type cannot be guessed', function (): void {
    $validated = true;

    $mimeTypes = $this->mock(MimeTypesInterface::class);
    $mimeTypes->expects('getExtensions')
        ->never();

    $uploadedFile = $this->mock(UploadedFile::class);
    $uploadedFile->expects('getClientOriginalName')
        ->once()
        ->andReturn(fake()->word());
    $uploadedFile->expects('getClientOriginalExtension')
        ->once()
        ->andReturn(fake()->fileExtension());
    $uploadedFile->expects('getMimeType')
        ->once()
        ->andReturn(null);

    $extensionMimeType = new ExtensionMimeType(new NullLogger(), $mimeTypes);
    $extensionMimeType->validate(fake()->word(), $uploadedFile, function () use (&$validated): void {
        $validated = false;
    });

    expect($validated)->toBeFalse();
});
