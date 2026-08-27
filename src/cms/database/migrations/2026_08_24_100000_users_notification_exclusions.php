<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            // jsonb, not json: Postgres has no equality operator for json, which
            // breaks every `select distinct "users".*` in the application.
            $table->jsonb('notification_exclusions')->default('[]');
        });
    }
};
