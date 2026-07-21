<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Authorization\Role;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Deterministic data for the handleiding screenshots.
 *
 * TestDataSeeder produces random faker content: record names like "deserunt"
 * and version numbers like #5391012 read as noise in a manual, and change on
 * every run so screenshots are never reproducible. This seeder layers
 * recognisable, stable content on top of it and guarantees the states the
 * figures actually need - in particular a record with a version history and a
 * pending mandate holder approval, which TestDataSeeder never creates.
 *
 * Run after TestDataSeeder:
 *   php artisan db:seed --class=TestDataSeeder
 *   php artisan db:seed --class=ScreenshotSeeder
 */
class ScreenshotSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Plausible Dutch processing-record names, in place of faker words. */
    private const RECORD_NAMES = [
        'Afhandelen burgervragen en klachten',
        'Cameratoezicht toegangsbeveiliging',
        'Declaratieverwerking medewerkers',
        'Inkoop- en leveranciersadministratie',
        'Klantcontact en dienstverlening',
        'Onderzoek vaccinatiegraad',
        'Personeelsdossier en verzuim',
        'Salarisadministratie',
        'Subsidieaanvragen beoordelen',
        'Toegangsbeheer informatiesystemen',
        'Verwerking sollicitatiegegevens',
        'Zorgregistratie cliëntdossiers',
    ];

    public function run(): void
    {
        $organisation = Organisation::query()->where('slug', 'nipg')->firstOrFail();

        $this->renameRecords($organisation);
        $this->createVersionHistory($organisation);
    }

    /**
     * Replace faker words with readable names, so register overviews look like
     * a real register rather than lorem ipsum.
     */
    private function renameRecords(Organisation $organisation): void
    {
        $records = AvgResponsibleProcessingRecord::query()
            ->where('organisation_id', $organisation->id)
            ->orderBy('id')
            ->take(count(self::RECORD_NAMES))
            ->get();

        foreach ($records as $index => $record) {
            $record->name = self::RECORD_NAMES[$index];
            $record->save();
        }
    }

    /**
     * The goedkeuringsproces figures need one record with a readable version
     * history: an established version 1 and an in-review version 2 awaiting a
     * mandate holder. Version numbers are sequential (1, 2) rather than the
     * factory's random integers.
     */
    private function createVersionHistory(Organisation $organisation): void
    {
        $record = AvgResponsibleProcessingRecord::query()
            ->where('organisation_id', $organisation->id)
            ->orderBy('id')
            ->firstOrFail();

        Snapshot::query()
            ->where('snapshot_source_type', $record::class)
            ->where('snapshot_source_id', $record->id)
            ->delete();

        $common = [
            'organisation_id' => $organisation->id,
            'snapshot_source_id' => $record->id,
            'snapshot_source_type' => $record::class,
            'name' => $record->name,
        ];

        Snapshot::factory()->create($common + [
            'version' => 1,
            'state' => Established::class,
            'replaced_at' => null,
        ]);

        $inReview = Snapshot::factory()->create($common + [
            'version' => 2,
            'state' => InReview::class,
            'replaced_at' => null,
        ]);

        // Prefer a dedicated mandate holder over the admin account, so the
        // "Toegewezen aan" column shows a realistic name rather than "Admin".
        $mandateHolder = User::query()
            ->whereHas('organisationRoles', static function ($query) use ($organisation): void {
                $query->where('organisation_id', $organisation->id)
                    ->where('role', Role::MANDATE_HOLDER->value);
            })
            ->where('email', '!=', 'admin@example.com')
            ->first();

        if ($mandateHolder === null) {
            return;
        }

        $mandateHolder->name = 'Marieke de Vries';
        $mandateHolder->save();

        SnapshotApproval::factory()->create([
            'snapshot_id' => $inReview->id,
            'assigned_to' => $mandateHolder->id,
            'status' => SnapshotApprovalStatus::UNKNOWN,
            'notified_at' => null,
        ]);
    }
}
