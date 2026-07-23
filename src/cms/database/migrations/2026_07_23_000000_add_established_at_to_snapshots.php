<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('snapshots', static function (Blueprint $table): void {
            $table->dateTime('established_at')->nullable()->after('replaced_at');
        });
    }

    public function down(): void
    {
        Schema::table('snapshots', static function (Blueprint $table): void {
            $table->dropColumn('established_at');
        });
    }
};
