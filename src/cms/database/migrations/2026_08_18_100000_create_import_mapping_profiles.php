<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mapping_profiles', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('target');

            // Signature of the source columns, used to recognise a known sheet.
            $table->string('source_fingerprint')->index();

            $table->json('mapping');

            // Profiles are immutable: editing stores a new version, so an
            // import can always be traced back to the mapping that produced it.
            $table->unsignedInteger('version')->default(1);

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'name', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mapping_profiles');
    }
};
