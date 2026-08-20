<?php

declare(strict_types=1);

use App\Services\SqlExport\IndexNameTruncater;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_periods', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations', indexName: IndexNameTruncater::foreignKey(
                    $table->getTable(),
                    'organisations',
                    'id',
                ))
                ->cascadeOnDelete();

            $table->text('name')->index();
            $table->boolean('enabled')->default(true);

            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_periods');
    }
};
