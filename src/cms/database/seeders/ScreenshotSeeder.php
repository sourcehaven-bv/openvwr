<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Authorization\Role;
use App\Enums\Dpia\MeasureType;
use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\RiskLevel;
use App\Enums\RegisterLayout;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Models\Organisation;
use App\Models\Receiver;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

use function array_keys;
use function count;
use function intdiv;
use function is_string;
use function sprintf;

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
    /**
     * Labels illustrate the indeling the handleiding explains: afdeling,
     * locatie and domein. Names from all three run together here, because a
     * record carries one of each and the label overview lists them side by
     * side.
     */
    private const TAG_NAMES = [
        // afdeling
        'HR',
        'Facilitair',
        'Infectiebestrijding',
        'Klantcontact',
        'Milieu & Veiligheid',
        // locatie
        'Hoofdkantoor',
        'Terminal Noord',
        'Locatie Bilthoven',
        // domein
        'Administratie',
        'Onderzoek',
        'Beveiliging',
    ];

    /** Plausible Dutch recipients of personal data. */
    private const RECEIVER_NAMES = [
        'Belastingdienst',
        'Gemeentelijke gezondheidsdienst',
        'Inspectie Gezondheidszorg en Jeugd',
        'Nationale politie',
        'Pensioenfonds ABP',
        'Rijksdienst voor Identiteitsgegevens',
        'UWV',
        'Zorgverzekeraar',
    ];

    /** Plausible Dutch processing goals. */
    private const GOAL_NAMES = [
        'Beantwoorden van vragen en klachten van burgers',
        'Beheer van toegangsrechten tot informatiesystemen',
        'Beoordelen van subsidieaanvragen',
        'Naleving van wettelijke bewaartermijnen',
        'Onderzoek naar de effectiviteit van beleid',
        'Uitvoeren van de salarisadministratie',
        'Verantwoording afleggen aan de toezichthouder',
        'Vaststellen van de identiteit van betrokkenen',
    ];

    /**
     * One label of each kind per record, so the figures show the afdeling /
     * locatie / domein combination the handleiding describes. DemoAvgRegisterSeeder
     * attaches tags at random, which leaves one record carrying nine labels and
     * others none - unusable in a screenshot.
     */
    private const RECORD_TAGS = [
        'Afhandelen burgervragen en klachten' => ['Klantcontact', 'Hoofdkantoor', 'Administratie'],
        'Cameratoezicht toegangsbeveiliging' => ['Facilitair', 'Terminal Noord', 'Beveiliging'],
        'Declaratieverwerking medewerkers' => ['HR', 'Hoofdkantoor', 'Administratie'],
        'Inkoop- en leveranciersadministratie' => ['Facilitair', 'Hoofdkantoor', 'Administratie'],
        'Klantcontact en dienstverlening' => ['Klantcontact', 'Terminal Noord', 'Administratie'],
        'Onderzoek vaccinatiegraad' => ['Infectiebestrijding', 'Locatie Bilthoven', 'Onderzoek'],
    ];

    /**
     * Labels are not limited to the verwerkingsregisters; the handleiding shows
     * the same field on Systemen/Applicaties. Those need readable descriptions
     * too - the factory generates lorem sentences.
     */
    private const SYSTEM_TAGS = [
        'Personeelsinformatiesysteem' => ['HR', 'Hoofdkantoor', 'Administratie'],
        'Salarispakket' => ['HR', 'Hoofdkantoor', 'Administratie'],
        'Cameratoezichtsysteem' => ['Facilitair', 'Terminal Noord', 'Beveiliging'],
        'Toegangscontrolesysteem' => ['Facilitair', 'Hoofdkantoor', 'Beveiliging'],
        'Onderzoeksdatabase vaccinaties' => ['Infectiebestrijding', 'Locatie Bilthoven', 'Onderzoek'],
        'Klantcontactsysteem' => ['Klantcontact', 'Hoofdkantoor', 'Administratie'],
    ];

    public function run(): void
    {
        $organisation = Organisation::query()->where('slug', 'nipg')->firstOrFail();

        $this->trimScreenshotUserRoles($organisation);
        $this->renameRecords($organisation);
        $this->renameRelatedEntities($organisation);
        $this->createVersionHistory($organisation);
        $this->createDpia($organisation);
        $this->applyTags($organisation);
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

        // The record detail page renders either as steps (a domain navigation
        // beside the form) or as one long stacked page, per user preference.
        // UserFactory picks one at random, so without pinning it the figure
        // alternates between two entirely different layouts between runs - and
        // the manual describes the steps layout.
        $user->register_layout = RegisterLayout::STEPS;
        $user->save();

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
        // Every record, not just the first dozen: the register overview sorts by
        // number and paginates, so renaming a subset leaves the very figure the
        // manual opens with showing faker words like "quidem" and "aspernatur".
        // The list is cycled, with a counter appended past the first pass to
        // keep names distinct.
        $records = AvgResponsibleProcessingRecord::query()
            ->where('organisation_id', $organisation->id)
            ->orderBy('id')
            ->get();

        $total = count(self::RECORD_NAMES);

        foreach ($records as $index => $record) {
            $name = self::RECORD_NAMES[$index % $total];
            $round = intdiv($index, $total);
            $record->name = $round === 0 ? $name : sprintf('%s (%d)', $name, $round + 1);
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

        // Receivers are described rather than named, so the column differs.
        $this->renameByOrganisation(
            Receiver::query()->where('organisation_id', $organisation->id),
            self::RECEIVER_NAMES,
            'description',
        );

        // Scrolling past the first domain reaches the goal descriptions and the
        // responsibility free text, both faker latin out of the box.
        $this->renameByOrganisation(AvgGoal::query(), self::GOAL_NAMES, 'goal');

        // The "Alle Versies" overview lists snapshots of every record type, not
        // just AVG responsible ones, so rename the other three too - otherwise
        // that figure still shows faker words in the "Naam versie" column.
        $this->renameByOrganisation(
            AvgProcessorProcessingRecord::query()->where('organisation_id', $organisation->id),
            self::RECORD_NAMES,
        );
        $this->renameByOrganisation(
            WpgProcessingRecord::query()->where('organisation_id', $organisation->id),
            self::RECORD_NAMES,
        );
        $this->renameByOrganisation(
            AlgorithmRecord::query()->where('organisation_id', $organisation->id),
            self::RECORD_NAMES,
        );

        // Snapshots carry their own name, shown in the "Alle Versies" overview.
        // They are created by TestDataSeeder from faker words, so rename them to
        // match the record they belong to.
        foreach (Snapshot::query()->where('organisation_id', $organisation->id)->get() as $snapshot) {
            $source = $snapshot->snapshotSource;

            if ($source === null) {
                continue;
            }

            $name = $source->getAttribute('name');

            if (!is_string($name)) {
                continue;
            }

            $snapshot->name = $name;
            $snapshot->save();
        }

        AvgResponsibleProcessingRecord::query()
            ->where('organisation_id', $organisation->id)
            ->update([
                'responsibility_distribution' => 'De verwerkingsverantwoordelijke bepaalt doel en middelen van de '
                    . 'verwerking. De uitvoering is belegd bij de betrokken directie.',
            ]);
    }

    /**
     * @param Builder<covariant Model> $query
     * @param list<string> $names
     */
    private function renameByOrganisation(Builder $query, array $names, string $column = 'name'): void
    {
        $models = $query->orderBy('id')->get();
        $total = count($names);

        foreach ($models as $index => $model) {
            $name = $names[$index % $total];
            $round = intdiv($index, $total);
            $model->setAttribute($column, $round === 0 ? $name : sprintf('%s (%d)', $name, $round + 1));
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

        // The approval panel on the version detail page is gated on the
        // SNAPSHOT_APPROVAL_UPDATE_PERSONAL permission, which is scoped per
        // organisation. A mandate holder whose role sits in another organisation
        // is assigned the approval but cannot see the Akkoord / Niet akkoord
        // buttons at all, so make sure the role exists for this one.
        $mandateHolder->organisationRoles()->firstOrCreate([
            'organisation_id' => $organisation->id,
            'role' => Role::MANDATE_HOLDER->value,
        ]);

        SnapshotApproval::factory()->create([
            'snapshot_id' => $inReview->id,
            'assigned_to' => $mandateHolder->id,
            'status' => SnapshotApprovalStatus::UNKNOWN,
            'notified_at' => null,
        ]);
    }

    /**
     * Give the named records and systems a predictable set of labels.
     *
     * Runs last: renameRelatedEntities() owns the label names themselves, and
     * renameRecords() the record names these keys match on.
     */
    private function applyTags(Organisation $organisation): void
    {
        // renameByOrganisation() can only rename as many labels as the demo
        // seeder happened to create, so the tail of TAG_NAMES may not exist
        // yet. Create what is missing rather than silently skipping it.
        $tags = Tag::query()
            ->where('organisation_id', $organisation->id)
            ->get()
            ->keyBy('name');

        foreach (self::TAG_NAMES as $name) {
            if ($tags->has($name)) {
                continue;
            }

            $tags->put($name, Tag::create([
                'name' => $name,
                'organisation_id' => $organisation->id,
            ]));
        }

        $records = AvgResponsibleProcessingRecord::query()
            ->where('organisation_id', $organisation->id)
            ->whereIn('name', array_keys(self::RECORD_TAGS))
            ->get();

        foreach ($records as $record) {
            $record->tags()->sync(self::tagIds($tags, self::RECORD_TAGS[$record->name]));
        }

        $systems = System::query()
            ->where('organisation_id', $organisation->id)
            ->orderBy('id')
            ->take(count(self::SYSTEM_TAGS))
            ->get();

        $systemNames = array_keys(self::SYSTEM_TAGS);

        foreach ($systems as $index => $system) {
            $name = $systemNames[$index] ?? null;

            if ($name === null) {
                continue;
            }

            $system->description = $name;
            $system->save();
            $system->tags()->sync(self::tagIds($tags, self::SYSTEM_TAGS[$name]));
        }
    }

    /**
     * Resolve label names to their ids, skipping any the seeder did not create.
     *
     * @param Collection<int|string, Tag> $tags
     * @param array<string> $names
     *
     * @return array<string>
     */
    private static function tagIds(Collection $tags, array $names): array
    {
        $ids = [];

        foreach ($names as $name) {
            $tag = $tags->get($name);

            if ($tag instanceof Tag) {
                $ids[] = $tag->id->toString();
            }
        }

        return $ids;
    }

    /**
     * A pre-scan that actually requires a DPIA, and the DPIA that follows from
     * it.
     *
     * TestDataSeeder creates neither, so without this the DPIA chapter has no
     * screen to photograph. The pre-scan ticks the "cameratoezicht" AP
     * criterion: one AP criterion is enough for PrescanEvaluator to return
     * REQUIRED, which is what makes the "DPIA starten" button appear - the
     * whole point of the figure. It also ties the example to the
     * "Cameratoezicht toegangsbeveiliging" record this seeder already renames,
     * so the manual reads as one story rather than unrelated fragments.
     */
    private function createDpia(Organisation $organisation): void
    {
        $name = 'Cameratoezicht toegangsbeveiliging';

        $prescan = DpiaPrescanRecord::factory()->create([
            'organisation_id' => $organisation->id,
            'name' => $name,
            'description' => 'Toets of het cameratoezicht bij de hoofdingang een DPIA '
                . 'vraagt.',
            'ap_criteria' => ['cameratoezicht'],
        ]);

        $dpia = DpiaRecord::factory()->create([
            'organisation_id' => $organisation->id,
            'dpia_prescan_record_id' => $prescan->id,
            'name' => $name,
            // DpiaRecord has no single description: the Rijksmodel splits it
            // over the paragraphs, and paragraaf 1 is the one the reader sees
            // first.
            'proposal_description' => 'Cameratoezicht op de toegang van het hoofdkantoor, '
                . 'gericht op het voorkomen van onbevoegde toegang.',
        ]);

        // Paragraaf 2: one ordinary and one special category, so the figure
        // shows both the plain case and the one that demands an exception
        // ground.
        $data = [
            ['Camerabeelden hoofdingang', PersonalDataType::ORDINARY, 'Bezoekers en medewerkers', 'Camera', '4 weken'],
            ['Biometrisch kenmerk gezicht', PersonalDataType::SPECIAL, 'Medewerkers', 'Toegangssysteem', '1 jaar'],
        ];

        foreach ($data as $i => [$description, $type, $subjects, $source, $retention]) {
            DpiaPersonalData::factory()->create([
                'organisation_id' => $organisation->id,
                'dpia_record_id' => $dpia->id,
                'description' => $description,
                'type' => $type,
                'data_subject_category' => $subjects,
                'source' => $source,
                'retention_period' => $retention,
                'order_column' => $i,
            ]);
        }

        // Paragraaf 16 and 17, with the link between them: the figure is about
        // a measure that lowers a risk, which only reads if they are coupled.
        $risk = DpiaRisk::factory()->create([
            'organisation_id' => $organisation->id,
            'dpia_record_id' => $dpia->id,
            'title' => 'Beelden langer bewaard dan nodig',
            'description' => 'Zonder automatische verwijdering blijven camerabeelden staan.',
            'origin' => 'Handmatig beheer van de opslag.',
            'likelihood' => RiskLevel::MEDIUM,
            'impact' => RiskLevel::HIGH,
            'order_column' => 0,
        ]);

        $measure = DpiaMeasure::factory()->create([
            'organisation_id' => $organisation->id,
            'dpia_record_id' => $dpia->id,
            'description' => 'Automatische verwijdering na vier weken.',
            'type' => MeasureType::TECHNICAL,
            'origin' => 'Bewaarbeleid cameratoezicht.',
            'residual_level' => RiskLevel::LOW,
            'owner' => 'Beheerder toegangssystemen',
            'order_column' => 0,
        ]);

        $measure->risks()->attach($risk);
    }
}
