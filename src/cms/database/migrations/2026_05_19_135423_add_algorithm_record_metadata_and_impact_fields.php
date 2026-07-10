<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('algorithm_records', static function (Blueprint $table): void {
            $table->date('meta_date_of_development')->nullable();
            $table->string('meta_owner_algorithm')->nullable();
            $table->string('meta_product_owner_algorithm')->nullable();

            $table->boolean('impact_with_consequences')->nullable();
            $table->boolean('impact_more_algorithms_applied')->nullable();
            $table->boolean('impact_effect_on_outcome')->nullable();
            $table->boolean('validation_answers_checked_by_product_owner')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('algorithm_records', static function (Blueprint $table): void {
            $table->dropColumn([
                'meta_date_of_development',
                'meta_owner_algorithm',
                'meta_product_owner_algorithm',
                'impact_with_consequences',
                'impact_more_algorithms_applied',
                'impact_effect_on_outcome',
                'validation_answers_checked_by_product_owner',
            ]);
        });
    }
};
