<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Audit events go to their own log channel (journald) instead of this table,
// out of reach of the application's database user.
//
// A table that still holds rows is left in place, with its append-only
// trigger: those rows are audit history that an operator must archive before
// removing the table by hand. See docs/audit_log.md.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs') && DB::table('audit_logs')->exists()) {
            return;
        }

        Schema::dropIfExists('audit_logs');
        DB::statement('DROP FUNCTION IF EXISTS audit_logs_append_only()');
    }
};
