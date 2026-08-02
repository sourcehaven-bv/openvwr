<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Paragraaf 2: the personal data, one row per category.
//
// The Rijksmodel asks to "classificeer deze persoonsgegevens naar: gewoon,
// gevoelig, bijzonder, strafrechtelijk en wettelijk identificatienummer".
// Storing that as a list rather than as prose is what lets paragraaf 12 derive
// which items need an exception ground, instead of asking the same question a
// second time and risking a different answer.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpia_personal_data', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignUuid('dpia_record_id')
                ->constrained('dpia_records')
                ->cascadeOnDelete();

            $table->text('description')->nullable(); // welk gegeven
            $table->string('type')->nullable(); // gewoon/gevoelig/bijzonder/...
            $table->text('data_subject_category')->nullable(); // categorie betrokkene
            $table->text('source')->nullable(); // bron van het gegeven
            $table->text('retention_period')->nullable(); // hoort bij paragraaf 10

            // Paragraaf 12: only relevant for bijzonder, strafrechtelijk and
            // wettelijk identificatienummer. Kept on the data item itself so the
            // ground stays with the gegeven it justifies.
            $table->text('exception_ground')->nullable();

            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_personal_data');
    }
};
