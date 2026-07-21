<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Authorization\Role;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    /** Plausible Dutch department names for the "AVG Verantwoordelijke Dienst" field. */
    private const SERVICE_NAMES = [
        'Directie Bedrijfsvoering',
        'Directie Communicatie',
        'Directie Informatievoorziening',
        'Directie Juridische Zaken',
        'Directie Personeel en Organisatie',
        'Directie Publieke Gezondheid',
    ];

    /** Plausible Dutch labels, in place of faker words. */
    private const TAG_NAMES = [
        'Bijzondere persoonsgegevens',
        'Cameratoezicht',
        'Extern gedeeld',
        'Financieel',
        'Gezondheidsgegevens',
        'Hoog risico',
        'Medewerkers',
        'Onderzoek',
        'Publiek toegankelijk',
        'Wettelijke verplichting',
    ];

    public function run(): void
    {
        $organisation = Organisation::query()->where('slug', 'nipg')->firstOrFail();

        $this->trimScreenshotUserRoles($organisation);
        $this->renameRecords($organisation);
        $this->renameRelatedEntities($organisation);
        $this->createVersionHistory($organisation);
    }

    /**
     * TestDataSeeder gives the admin every organisation role, including
     * Functionaris Gegevensbescherming. That role adds an "FG Opmerkingen"
     * widget to entity pages which only FGs ever see, so leaving it on would
     * put a field in the manual that is irrelevant to most readers.
     *
     * The screenshots are taken as a (Chief) Privacy Officer, which is the role
     * the manual describes for these screens.
     */
    private function trimScreenshotUserRoles(Organisation $organisation): void
    {
        $user = User::query()->where('email', 'admin@example.com')->first();

        if ($user === null) {
            return;
        }

        $user->organisationRoles()
            ->where('organisation_id', $organisation->id)
            ->where('role', Role::DATA_PROTECTION_OFFICIAL->value)
            ->delete();
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
            ->take(\count(self::RECORD_NAMES))
            ->get();

        foreach ($records as $index => $record) {
            $record->name = self::RECORD_NAMES[$index];
            $record->save();
        }
    }

    /**
     * The detail-page figure shows more than the record's own name: the
     * "AVG Verantwoordelijke Dienst" dropdown and the Labels field render
     * related entities, which TestDataSeeder fills with faker latin
     * ("Quas ut laudantium id dignissimos temporibus et architecto.",
     * labels reading "quidem", "est", "id"). Rename those too, or the figure
     * still reads as noise however well the record itself is named.
     */
    private function renameRelatedEntities(Organisation $organisation): void
    {
        $this->renameByOrganisation(
            AvgResponsibleProcessingRecordService::query()->where('organisation_id', $organisation->id),
            self::SERVICE_NAMES,
        );

        $this->renameByOrganisation(
            Tag::query()->where('organisation_id', $organisation->id),
            self::TAG_NAMES,
        );
    }

    /**
     * @param Builder<covariant Model> $query
     * @param list<string> $names
     */
    private function renameByOrganisation(Builder $query, array $names): void
    {
        $models = $query->orderBy('id')->take(\count($names))->get();

        foreach ($models as $index => $model) {
            $model->setAttribute('name', $names[$index]);
            $model->save();
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
