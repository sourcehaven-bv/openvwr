<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// `snapshots.organisation_id` was de laatste verwijzing naar `organisations`
// zonder ON DELETE CASCADE: 34 van de 35 tabellen met een `organisation_id`
// cascaden, alleen deze niet. Dat blokkeerde het definitief opruimen van een
// organisatie -- de FK weigerde de delete in plaats van mee te gaan.
//
// Dit is een omissie, geen ontwerpkeuze. `create_snapshotable_tables` schreef
// `->constrained('organisations')` zonder `->cascadeOnDelete()`, terwijl de
// migraties van diezelfde week (tags, processors, addresses, contact_persons,
// ...) die aanroep wel hebben. Dezelfde fout stond in
// `create_processing_records_table` en is daar later hersteld; snapshots is bij
// die opruiming overgeslagen.
//
// Meegaan met de organisatie is ook inhoudelijk juist: Snapshot is TenantAware,
// en alle vier de mogelijke `snapshot_source_type`s (AlgorithmRecord,
// AvgProcessorProcessingRecord, AvgResponsibleProcessingRecord,
// WpgProcessingRecord) cascaden zelf al. Een achterblijvende snapshot zou dus
// gegarandeerd naar een verdwenen bron wijzen.
//
// De onderliggende tabellen (snapshot_data, snapshot_transitions,
// snapshot_approvals, snapshot_approval_logs, related_snapshot_sources) staan
// al op CASCADE richting `snapshots` en volgen hierdoor vanzelf mee.

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('snapshots', static function (Blueprint $table): void {
            $table->dropForeign('snapshots_organisation_id_foreign');

            $table->foreignUuid('organisation_id')
                ->change()
                ->constrained('organisations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('snapshots', static function (Blueprint $table): void {
            $table->dropForeign('snapshots_organisation_id_foreign');

            $table->foreignUuid('organisation_id')
                ->change()
                ->constrained('organisations');
        });
    }
};
