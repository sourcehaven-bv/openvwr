<?php

declare(strict_types=1);

use App\Models\States\DataBreachRecord\Reported;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            $table->string('state')->default(Reported::$name);
        });
    }

    public function down(): void
    {
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            $table->dropColumn('state');
        });
    }
};
