<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Upload;

use App\Rules\ExtensionMimeType;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypesInterface;

class PosterFileField extends SpatieMediaLibraryFileUpload
{
    public static function make(string $name): static
    {
        return parent::make($name)
            ->imageEditor()
            ->imagePreviewHeight('295')
            ->imageResizeMode('cover')
            ->imageCropAspectRatio('33:8')
            ->imageResizeTargetWidth('1920')
            ->imageResizeTargetHeight('480')
            ->panelAspectRatio('33:8')
            ->panelLayout('integrated')
            ->columnSpanFull()
            ->image()
            ->rules([
                static function (LoggerInterface $logger, MimeTypesInterface $mimeTypes): ExtensionMimeType {
                    return new ExtensionMimeType($logger, $mimeTypes);
                },
                'image',
            ]);
    }
}
