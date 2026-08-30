<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Config\Feature;
use App\Filament\Resources\WpgProcessingRecordResource;
use Illuminate\Database\Eloquent\Model;

class WpgProcessingRecordRelationManager extends RelationManager
{
    protected static string $languageFile = 'wpg_processing_record';
    protected static string $relationship = 'wpgProcessingRecords';
    protected static string $resource = WpgProcessingRecordResource::class;

    /**
     * Hides the WPG tab on every record hosting this relation manager — the
     * documents, systems, tags and the other core entities — when the WPG
     * feature is switched off.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Feature::wpgEnabled() && parent::canViewForRecord($ownerRecord, $pageClass);
    }
}
