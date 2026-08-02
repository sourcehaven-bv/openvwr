<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The DPIA register, following the 17 paragraphs of Model DPIA Rijksdienst v3.0
// (september 2023). The paragraph numbers in the comments below are the
// official ones and are also used as the form step keys, so the record stays
// traceable to the model that auditors, FGs and the AP work with.
//
// Part A (1-10) facts Part B (11-15) lawfulness
// Part C (16) risks Part D (17) measures
//
// Paragraphs 16 and 17 are repeatable and live in their own tables
// (dpia_risks / dpia_measures), because a measure has to state which risk it
// addresses and what risk remains afterwards.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpia_records', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->foreignUuid('entity_number_id')
                ->nullable()
                ->constrained('entity_numbers')
                ->cascadeOnDelete();

            $table->string('name')->index();

            // Type of DPIA: regelgeving (wetten/AMvB/ministeriele regelingen) or
            // a processing carried out by (or on behalf of) the organisation.
            $table->string('subject_type')->default('verwerking');

            // A. Beschrijving algemene kenmerken gegevensverwerkingen
            $table->text('proposal_description')->nullable(); // 1
            $table->text('proposal_motivation')->nullable(); // 1
            $table->text('personal_data_description')->nullable(); // 2
            $table->text('personal_data_sources')->nullable(); // 2
            $table->text('processing_description')->nullable(); // 3
            $table->text('techniques_description')->nullable(); // 4
            $table->boolean('automated_decision_making')->default(false); // 4
            $table->boolean('profiling')->default(false); // 4
            $table->boolean('cloud_processing')->default(false); // 4
            $table->boolean('big_data_processing')->default(false); // 4
            $table->text('techniques_explanation')->nullable(); // 4
            $table->text('purpose_description')->nullable(); // 5
            $table->text('parties_description')->nullable(); // 6
            $table->text('parties_access')->nullable(); // 6
            $table->text('interests_description')->nullable(); // 7
            $table->text('interests_data_subjects')->nullable(); // 7
            $table->text('processing_locations')->nullable(); // 8
            $table->boolean('outside_eea')->default(false); // 8
            $table->text('transfer_mechanism')->nullable(); // 8
            $table->text('transfer_safeguards')->nullable(); // 8
            $table->text('legal_policy_framework')->nullable(); // 9
            $table->text('retention_periods')->nullable(); // 10
            $table->text('retention_motivation')->nullable(); // 10
            $table->text('retention_responsible')->nullable(); // 10

            // B. Beoordeling rechtmatigheid gegevensverwerkingen
            $table->text('legal_basis')->nullable(); // 11
            $table->text('legal_basis_conditions')->nullable(); // 11
            $table->boolean('special_categories')->default(false); // 12
            $table->text('special_categories_exception')->nullable(); // 12
            $table->boolean('national_identification_number')->default(false); // 12
            $table->text('national_identification_number_basis')->nullable(); // 12
            $table->boolean('further_processing')->default(false); // 13
            $table->text('purpose_limitation')->nullable(); // 13
            $table->text('necessity_proportionality')->nullable(); // 14
            $table->text('necessity_subsidiarity')->nullable(); // 14
            $table->text('data_subject_rights_procedure')->nullable(); // 15
            $table->boolean('rights_restricted')->default(false); // 15
            $table->text('rights_restriction_basis')->nullable(); // 15

            // C/D. Toelichting bij de risico's en maatregelen; de risico's en
            // maatregelen zelf staan in dpia_risks / dpia_measures.
            $table->text('risks_additional_information')->nullable(); // 16.2
            $table->text('measures_additional_information')->nullable();// 17.2
            $table->text('residual_risk_acceptance')->nullable(); // 17.3

            // Proces: consultatie, FG-advies en voorafgaande raadpleging AP.
            // Deze horen bij het proceskader (deel I) en niet bij de 17 punten,
            // maar zonder vastlegging is de DPIA niet verantwoordbaar.
            $table->boolean('data_subjects_consulted')->default(false);
            $table->text('data_subjects_consultation')->nullable();
            $table->text('fg_advice')->nullable();
            $table->text('fg_advice_followup')->nullable();
            $table->date('fg_advice_received_at')->nullable();
            $table->boolean('ap_consultation_required')->default(false);
            $table->text('ap_consultation')->nullable();
            $table->date('ap_consultation_requested_at')->nullable();

            // Herziening: de EDPB adviseert iedere drie jaar opnieuw te kijken,
            // ook als er niets is gewijzigd.
            $table->date('assessed_at')->nullable();
            $table->date('review_at')->nullable();

            $table->text('management_summary')->nullable();
            $table->string('import_id')->nullable();
            $table->string('import_number')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 16. Risico's voor betrokkenen. Risiconiveau volgt uit kans x impact,
        // maar wordt apart opgeslagen: de invuller mag beargumenteerd afwijken
        // van de matrix (zie de toelichting bij de risicomatrix in het model).
        Schema::create('dpia_risks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignUuid('dpia_record_id')
                ->constrained('dpia_records')
                ->cascadeOnDelete();

            $table->text('description')->nullable(); // 16.1.1
            $table->text('origin')->nullable(); // 16.1.2
            $table->string('likelihood')->nullable(); // 16.1.3
            $table->text('likelihood_motivation')->nullable();// 16.1.4
            $table->string('impact')->nullable(); // 16.1.5
            $table->text('impact_motivation')->nullable(); // 16.1.6
            $table->string('level')->nullable(); // 16.1.7
            $table->text('level_motivation')->nullable(); // 16.1.8

            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });

        // 17. Maatregelen. residual_level is het risico dat overblijft nadat
        // deze maatregel is uitgevoerd; is dat 'hoog', dan moet de AP
        // voorafgaand geraadpleegd worden (artikel 36 AVG).
        Schema::create('dpia_measures', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignUuid('dpia_record_id')
                ->constrained('dpia_records')
                ->cascadeOnDelete();

            $table->text('description')->nullable(); // 17.1.3
            $table->string('type')->nullable(); // technisch/organisatorisch/juridisch
            $table->text('origin')->nullable(); // 17.1.2
            $table->string('residual_level')->nullable(); // 17.1.4
            $table->text('ap_advice')->nullable(); // 17.1.5
            $table->string('monitoring_country')->nullable(); // 17.1.6
            $table->text('owner')->nullable(); // 17.1.7

            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });

        // Welke maatregel pakt welk risico aan (punt 17, expliciet vereist).
        Schema::create('dpia_measure_risk', static function (Blueprint $table): void {
            $table->foreignUuid('dpia_measure_id')
                ->constrained('dpia_measures')
                ->cascadeOnDelete();
            $table->foreignUuid('dpia_risk_id')
                ->constrained('dpia_risks')
                ->cascadeOnDelete();

            $table->unique(['dpia_measure_id', 'dpia_risk_id']);
        });

        // Koppeling van een DPIA aan verwerkingen, systemen en ander materiaal.
        // Polymorf, zodat dezelfde DPIA aan meerdere verwerkingen kan hangen:
        // een DPIA mag een reeks vergelijkbare verwerkingen bestrijken
        // (artikel 35, eerste lid, AVG en overweging 92).
        Schema::create('dpia_record_relatables', static function (Blueprint $table): void {
            $table->foreignUuid('dpia_record_id')
                ->constrained('dpia_records')
                ->cascadeOnDelete();

            $table->uuidMorphs('dpia_record_relatable');
            $table->unique(
                ['dpia_record_id', 'dpia_record_relatable_id', 'dpia_record_relatable_type'],
                'dpia_record_relatables_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_record_relatables');
        Schema::dropIfExists('dpia_measure_risk');
        Schema::dropIfExists('dpia_measures');
        Schema::dropIfExists('dpia_risks');
        Schema::dropIfExists('dpia_records');
    }
};
