<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// A short name for the risk, next to its full description.
//
// Paragraaf 17 offers the risks as checkboxes, and a multi-sentence description
// makes an unreadable label. The title is what appears in those checkboxes, in
// the repeater header and in the aandachtspunten; the description keeps the
// reasoning.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpia_risks', static function (Blueprint $table): void {
            $table->string('title')->nullable()->after('dpia_record_id');
        });

        // Existing risks only have a description. Use its first line as the
        // title so nothing shows up unnamed; the invuller can shorten it.
        DB::statement(<<<'SQL'
            UPDATE dpia_risks
            SET title = LEFT(SPLIT_PART(description, E'\n', 1), 255)
            WHERE description IS NOT NULL AND description <> ''
        SQL);
    }

    public function down(): void
    {
        Schema::table('dpia_risks', static function (Blueprint $table): void {
            $table->dropColumn('title');
        });
    }
};
