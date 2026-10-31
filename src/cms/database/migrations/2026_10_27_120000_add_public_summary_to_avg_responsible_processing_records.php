<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Een korte uitleg in gewone taal voor inwoners. De publieke website toont die
// bovenaan de verwerking; zonder uitleg vallen thema's terug op de doelen.

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avg_responsible_processing_records', static function (Blueprint $table): void {
            $table->text('public_summary')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('avg_responsible_processing_records', static function (Blueprint $table): void {
            $table->dropColumn('public_summary');
        });
    }
};
