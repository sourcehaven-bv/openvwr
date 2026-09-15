<?php

declare(strict_types=1);

namespace App\Services\Cleanup;

use App\Models\Address;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Remark;
use App\Models\Responsible;
use App\Models\ResponsibleLegalEntity;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Vaste verwijdervolgorde voor het definitief opruimen van soft-deleted records.
 *
 * Waarom een expliciete lijst en geen automatische ontdekking: het schema kent
 * foreign keys zonder ON DELETE-clausule. PostgreSQL hanteert dan NO ACTION, en
 * die blokkeren een hard delete van de ouder zolang er kinderen bestaan. Een
 * willekeurige volgorde loopt dus vast op een constraint-violation.
 *
 * De volgorde is kind-vóór-ouder, afgeleid uit de FK-graaf. Query om hem
 * opnieuw af te leiden na een schemawijziging:
 *
 *   SELECT c.conrelid::regclass AS kind, c.confrelid::regclass AS ouder
 *   FROM pg_constraint c
 *   WHERE c.contype = 'f' AND c.confdeltype <> 'c';
 *
 * De blokkerende randen tussen modellen die daadwerkelijk soft-deleten zijn:
 *
 *   remarks -> users (NO ACTION)
 *   organisations -> responsible_legal_entity (NO ACTION)
 *   dpia_records -> dpia_prescan_records (SET NULL, dus niet blokkerend)
 *
 * De overige NO ACTION-randen wijzen naar lookup-tabellen (`document_types`,
 * `contact_person_positions`, `algorithm_statuses`, `*_record_service`, ...).
 * Die tabellen hébben wel een `deleted_at`-kolom, maar hun modellen gebruiken
 * `HasSoftDeletes` niet, dus ze raken nooit soft-deleted en komen hier niet
 * voor. Ze staan bewust niet in deze lijst: een forceDelete erop zou records
 * weggooien die nooit als verwijderd zijn gemarkeerd.
 *
 * @see SoftDeleteCleaner
 */
final class SoftDeleteCleanupOrder
{
    /**
     * Modellen met `parent_id` naar de eigen tabel, zonder ON DELETE CASCADE.
     * Binnen zo'n tabel moet diepste-eerst worden verwijderd.
     */
    public const array SELF_REFERENCING = [
        AlgorithmRecord::class,
        AvgProcessorProcessingRecord::class,
        AvgResponsibleProcessingRecord::class,
        WpgProcessingRecord::class,
    ];

    /**
     * Verwijdervolgorde: kinderen eerst, ouders later.
     *
     * Organisation staat als voorlaatste. Vrijwel alle tenant-tabellen hangen
     * met ON DELETE CASCADE aan `organisations`, dus een organisatie opruimen
     * sleept de rest mee. Door hem laat te nemen zijn de onderliggende records
     * al netjes opgeruimd -- inclusief hun bestanden in de documentopslag --
     * in plaats van stilzwijgend door de database weggecascadeerd, wat de
     * bijbehorende bestanden zou achterlaten.
     *
     * @return list<class-string<Model>>
     */
    public static function models(): array
    {
        return [
            // 1. Bladeren: hier verwijst binnen deze lijst niets blokkerend naar.
            //    Remark moet vóór User, want remarks.user_id is NO ACTION.
            Address::class,
            Remark::class,
            Tag::class,

            // 2. Registerinhoud.
            AlgorithmRecord::class,
            AvgProcessorProcessingRecord::class,
            AvgResponsibleProcessingRecord::class,
            WpgProcessingRecord::class,
            DpiaRecord::class,
            DataBreachRecord::class,
            Document::class,

            // 3. Doelen en betrokkenen.
            AvgGoal::class,
            WpgGoal::class,
            StakeholderDataItem::class,
            Stakeholder::class,

            // 4. Entiteiten waar de registerinhoud naar verwijst.
            ContactPerson::class,
            Responsible::class,
            Receiver::class,
            Processor::class,
            System::class,

            // 5. DpiaPrescanRecord ná DpiaRecord: de FK is SET NULL, dus andersom
            //    zou de koppeling stilzwijgend gewist worden.
            DpiaPrescanRecord::class,

            // 6. Gebruikers, ná Remark.
            User::class,

            // 7. Organisatie en haar juridische entiteit, als laatste.
            Organisation::class,
            ResponsibleLegalEntity::class,
        ];
    }
}
