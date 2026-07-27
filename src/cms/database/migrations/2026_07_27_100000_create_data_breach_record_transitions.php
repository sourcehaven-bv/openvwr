<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_breach_record_transitions', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_breach_record_id')
                ->constrained('data_breach_records')
                ->cascadeOnDelete();
            // Nullable: transitions can also be made outside a web request
            // (seeders, console commands), where there is no acting user.
            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users');

            $table->string('state');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_breach_record_transitions');
    }
};
