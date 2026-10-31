<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\MarkdownEditor;

use Filament\Forms\Components\MarkdownEditor as FilamentMarkdownEditor;

class MarkdownEditor extends FilamentMarkdownEditor
{
    public static function make(?string $name = null): static
    {
        $toolbarButtons = [
            'bold',
            'bulletList',
            'heading',
            'italic',
            'link',
            'orderedList',
        ];

        return parent::make($name)
            ->toolbarButtons($toolbarButtons);
    }
}
