<?php

declare(strict_types=1);

use App\Services\SqlExport\IndexNameTruncater;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
                $table->uuid('origin_id')->nullable();
            });

            DB::statement(sprintf(
                'CREATE UNIQUE INDEX %s ON %s (organisation_id, origin_id) WHERE deleted_at IS NULL;',
                IndexNameTruncater::unique($table, 'organisation_id', 'origin_id'),
                $table,
            ));
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement(sprintf(
                'DROP INDEX IF EXISTS %s;',
                IndexNameTruncater::unique($table, 'organisation_id', 'origin_id'),
            ));

            Schema::table($table, static function (Blueprint $table): void {
                $table->dropColumn('origin_id');
            });
        }
    }
};
