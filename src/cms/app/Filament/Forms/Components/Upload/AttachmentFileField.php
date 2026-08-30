<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Upload;

use App\Config\Config;
use App\Enums\Media\MediaGroup;
use App\Facades\Authentication;
use App\Rules\ExtensionMimeType;
use App\Rules\Virusscanner;
use App\Services\Virusscanner\Virusscanner as VirusscannerService;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypesInterface;
use Webmozart\Assert\Assert;

use function __;

class AttachmentFileField extends SpatieMediaLibraryFileUpload
{
    public const MEGABYTE = 1024 * 1024;

    public static function make(?string $name = null): static
    {
        $types = Config::array('media-library.permitted_file_types.attachment');
        Assert::allString($types);

        $allowedExtensions = Config::array('media-library.permitted_file_extensions.attachment');
        /** @var array<string, list<string>> $allowedExtensions */

        return parent::make($name)
            ->label(__('general.attachments'))
            ->collection(MediaGroup::ATTACHMENTS->value)
            ->downloadable()
            ->multiple()
            ->acceptedFileTypes($types)
            ->mimeTypeMap(self::buildMimeTypeMap($allowedExtensions))
            ->maxSize(self::MEGABYTE * 20)
            ->preserveFilenames()
            ->properties([
                'organisation_id' => Authentication::organisation()->id->toString(),
            ])
            ->rules([
                static function (LoggerInterface $logger, MimeTypesInterface $mimeTypes): ExtensionMimeType {
                    return new ExtensionMimeType($logger, $mimeTypes);
                },
                static function (LoggerInterface $logger, VirusscannerService $virusscanner): Virusscanner {
                    return new Virusscanner($logger, $virusscanner);
                },
            ])
            ->columnSpanFull();
    }

    /**
     * @param array<string, list<string>> $allowedExtensions
     *
     * @return array<string, string>
     */
    private static function buildMimeTypeMap(array $allowedExtensions): array
    {
        $map = [];
        foreach ($allowedExtensions as $mimeType => $extensions) {
            foreach ($extensions as $extension) {
                $map[$extension] = $mimeType;
            }
        }

        return $map;
    }
}
