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
        Schema::table('algorithm_records', static function (Blueprint $table): void {
            $table->dropForeign(IndexNameTruncater::foreignKey(
                'algorithm_records',
                'algorithm_meta_schemas',
                'id',
            ));
            $table->dropColumn(['algorithm_meta_schema_id', 'meta_lang']);
        });

        Schema::dropIfExists('algorithm_meta_schemas');
    }
};
