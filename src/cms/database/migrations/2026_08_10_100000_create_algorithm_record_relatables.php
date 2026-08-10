<?php

declare(strict_types=1);

use App\Services\SqlExport\IndexNameTruncater;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The processing registers that can link algorithms. The link hangs on the
     * processing, not on the system: one system may hold several algorithms that
     * are not all relevant to every processing done with that system.
     */
    private const TABLES = [
        'avg_responsible_processing_records',
        'avg_processor_processing_records',
        'wpg_processing_records',
    ];

    public function up(): void
    {
        Schema::create('algorithm_record_relatables', static function (Blueprint $table): void {
            $algorithmRecordFk = IndexNameTruncater::foreignKey(
                $table->getTable(),
                'algorithm_records',
                'id',
            );
            $table->foreignUuid('algorithm_record_id')
                ->constrained('algorithm_records', indexName: $algorithmRecordFk)
                ->cascadeOnDelete();

            $algorithmRecordRelatableIx = IndexNameTruncater::foreignKey(
                $table->getTable(),
                'algorithm_record_relatable_type',
                'algorithm_record_relatable_id',
            );
            $table->uuidMorphs('algorithm_record_relatable', $algorithmRecordRelatableIx);

            $table->timestamps();
        });

        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->boolean('has_algorithms')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->dropColumn('has_algorithms');
            });
        }

        Schema::dropIfExists('algorithm_record_relatables');
    }
};
