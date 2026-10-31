<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Een snapshot heeft hoogstens één open publicatie (`end_date IS NULL`). De
// verwerking van de publicatiecontrole deed eerst een lookup en daarna een
// insert, zonder constraint. Twee controles tegelijk (elke node heeft een eigen
// queue) konden zo twee open entries voor dezelfde snapshot aanmaken.
//
// Bestaande dubbele open entries beschrijven dezelfde publicatieperiode. We
// houden per snapshot de oudste (vroegste `start_date`) en verwijderen de rest;
// afsluiten zou een onderbreking in de historie suggereren die er niet was.

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            DELETE FROM static_website_snapshot_entries
            WHERE id IN (
                SELECT id FROM (
                    SELECT id, row_number() OVER (
                        PARTITION BY snapshot_id ORDER BY start_date, created_at, id
                    ) AS position
                    FROM static_website_snapshot_entries
                    WHERE end_date IS NULL
                ) AS open_entries
                WHERE position > 1
            )
            SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX static_website_snapshot_entries_open_unique
            ON static_website_snapshot_entries (snapshot_id)
            WHERE end_date IS NULL
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX static_website_snapshot_entries_open_unique');
    }
};
