<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bring audit_logs in line with minvws/laravel-logging.
//
// The table was copied from the package before the package renamed `context`
// to `request` and `pii_context` to `pii_request`. The package does not load
// its own migrations, so the rename never ran here, and every insert by its
// database logger would fail on the missing columns.
//
// The index serves "last successful login per user":
// event_code = '091111' AND failed = false, grouped by or filtered on email.

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', static function (Blueprint $table): void {
            $table->renameColumn('context', 'request');
            $table->renameColumn('pii_context', 'pii_request');
            $table->index(['event_code', 'email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', static function (Blueprint $table): void {
            $table->dropIndex(['event_code', 'email', 'created_at']);
            $table->renameColumn('request', 'context');
            $table->renameColumn('pii_request', 'pii_context');
        });
    }
};
