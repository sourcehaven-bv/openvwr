<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The copyable entities (same set that carries origin_id). last_synced_at records
     * when a row was last written by a cross-org copy, so a later re-copy can tell an
     * untouched copy (safe to overwrite silently) from a locally edited one (must ask).
     */
    private const TABLES = [
        'avg_responsible_processing_records',
        'avg_processor_processing_records',
        'wpg_processing_records',
        'algorithm_records',
        'data_breach_records',
        'processors',
        'receivers',
        'responsibles',
        'systems',
        'contact_persons',
        'documents',
        'stakeholders',
        'stakeholder_data_items',
        'tags',
        'avg_goals',
        'wpg_goals',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->timestamp('last_synced_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->dropColumn('last_synced_at');
            });
        }
    }
};
