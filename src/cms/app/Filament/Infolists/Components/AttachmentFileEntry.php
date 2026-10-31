<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function __;

class AttachmentFileEntry extends TextEntry
{
    protected string $view = 'filament.infolists.components.entries.attachment_file_entry';

    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label(__('general.attachments'))
            ->placeholder(__('general.none_selected'));
    }
}
