<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Make audit_logs append-only. The application may insert rows, but any
// UPDATE, DELETE or TRUNCATE raises an error. This also blocks raw queries,
// not just Eloquent.
//
// The table owner can still disable or drop these triggers. For retention, a
// DBA can disable the trigger in a transaction. See docs/audit_log.md.

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE FUNCTION audit_logs_append_only() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'audit_logs is append-only: % is not allowed', TG_OP
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$
            SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER audit_logs_append_only
            BEFORE UPDATE OR DELETE OR TRUNCATE ON audit_logs
            FOR EACH STATEMENT EXECUTE FUNCTION audit_logs_append_only()
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER audit_logs_append_only ON audit_logs');
        DB::statement('DROP FUNCTION audit_logs_append_only()');
    }
};
