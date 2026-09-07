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
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            $table->string('import_id')->nullable();

            $table->unique(
                ['organisation_id', 'import_id'],
                IndexNameTruncater::unique('data_breach_records', 'organisation_id', 'import_id'),
            );
        });
    }

    public function down(): void
    {
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            $table->dropUnique(
                IndexNameTruncater::unique('data_breach_records', 'organisation_id', 'import_id'),
            );
            $table->dropColumn('import_id');
        });
    }
};
