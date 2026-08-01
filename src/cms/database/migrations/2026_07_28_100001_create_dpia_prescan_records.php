<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Pre-scan DPIA: the threshold assessment that decides whether a DPIA is
// mandatory. It is a register in its own right because the Rijksmodel requires
// a negative outcome to be documented and archived too (deel I, paragraaf 1.6):
// "Wanneer de Pre-Scan DPIA ertoe leidt dat geen DPIA uitgevoerd moet worden is
// het van belang dat de uitgevoerde Pre-Scan gedocumenteerd en gearchiveerd is."
//
// The criteria are stored as two jsonb arrays of selected keys rather than one
// boolean column per criterion: the AP and EDPB lists are external and change
// over time, and the counting rules only care how many were selected.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpia_prescan_records', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->foreignUuid('entity_number_id')
                ->nullable()
                ->constrained('entity_numbers')
                ->cascadeOnDelete();

            $table->string('name')->index();
            $table->text('description')->nullable();

            // Grondslag-ingangen uit paragraaf 1.2 van het Rijksmodel.
            $table->boolean('new_legislation')->default(false);
            $table->boolean('departmental_policy')->default(false);
            $table->boolean('public_cloud')->default(false);

            // AP- en EDPB-criteria (opgeslagen als lijst van gekozen sleutels).
            $table->jsonb('ap_criteria')->default(DB::raw("'[]'::jsonb"));
            $table->jsonb('edpb_criteria')->default(DB::raw("'[]'::jsonb"));

            // Internationale doorgifte, bepaalt of een DTIA nodig is.
            $table->boolean('international_transfer')->default(false);
            $table->boolean('outside_eea')->default(false);
            $table->string('transfer_mechanism')->nullable();

            // Kinderen/digitale dienst, bepaalt of een KIA wenselijk is.
            $table->boolean('digital_service')->default(false);
            $table->boolean('minors')->default(false);

            // Algoritmes, bepaalt of een IAMA wenselijk is.
            $table->boolean('algorithm')->default(false);
            $table->boolean('high_risk_ai')->default(false);

            // Uitkomst. Wordt bij opslaan afgeleid uit de antwoorden, maar
            // vastgelegd zodat later te zien is wat er destijds uit kwam.
            $table->string('outcome')->nullable();
            $table->text('outcome_motivation')->nullable();
            $table->date('assessed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Een pre-scan leidt tot (hooguit) één DPIA; de DPIA houdt de verwijzing
        // vast zodat de aanleiding traceerbaar blijft.
        Schema::table('dpia_records', static function (Blueprint $table): void {
            $table->foreignUuid('dpia_prescan_record_id')
                ->nullable()
                ->constrained('dpia_prescan_records')
                ->nullOnDelete();
        });

        Schema::create('dpia_prescan_record_relatables', static function (Blueprint $table): void {
            $table->foreignUuid('dpia_prescan_record_id')
                ->constrained('dpia_prescan_records')
                ->cascadeOnDelete();

            $table->uuidMorphs('dpia_prescan_record_relatable');
            $table->unique(
                [
                    'dpia_prescan_record_id',
                    'dpia_prescan_record_relatable_id',
                    'dpia_prescan_record_relatable_type',
                ],
                'dpia_prescan_record_relatables_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_prescan_record_relatables');

        Schema::table('dpia_records', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('dpia_prescan_record_id');
        });

        Schema::dropIfExists('dpia_prescan_records');
    }
};
