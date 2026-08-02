<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Which artikel 27 AI-verordening categories apply.
//
// "Is dit hoog-risico AI?" is hard to answer from the article text but easy to
// answer from the categories, so the categories are asked instead and the
// boolean is derived from them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpia_prescan_records', static function (Blueprint $table): void {
            $table->jsonb('high_risk_ai_categories')->default(DB::raw("'[]'::jsonb"));
        });
    }

    public function down(): void
    {
        Schema::table('dpia_prescan_records', static function (Blueprint $table): void {
            $table->dropColumn('high_risk_ai_categories');
        });
    }
};
