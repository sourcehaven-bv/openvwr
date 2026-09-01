<?php

declare(strict_types=1);

namespace App\Services\Cleanup;

use App\Config\Config;
use App\Enums\Media\MediaGroup;
use App\Vendor\MediaLibrary\Media;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Webmozart\Assert\Assert;

use function class_uses_recursive;
use function in_array;

/**
 * Ruimt soft-deleted records definitief op (forceDelete).
 *
 * Twee dingen die een kale `forceDelete()` niet doet en deze service wel:
 *
 * 1. **Bestanden.** Bijlagen staan in de documentopslag (media-library disk).
 *    Die volgen niet mee met een database-delete. `media.organisation_id` heeft
 *    zelfs ON DELETE CASCADE, dus het opruimen van een organisatie gooit de
 *    media-rijen weg terwijl de bestanden blijven staan. Daarom verwijderen we
 *    de media expliciet vóór het record; Spatie's Media-model verwijdert dan
 *    ook het onderliggende bestand. Dat werkt op de lokale schijf én op S3,
 *    omdat beide via dezelfde Flysystem-disk lopen.
 *
 * 2. **Volgorde.** Zie SoftDeleteCleanupOrder voor de FK-onderbouwing.
 *
 * Het auditlog blijft bewust buiten schot; zie de opmerking bij
 * cleanupExpired().
 */
class SoftDeleteCleaner
{
    /**
     * Ruimt alle records op waarvan de soft delete langer geleden is dan de
     * ingestelde bewaartermijn.
     *
     * Het auditlog wordt hier bewust NIET meegenomen. De verantwoordingsplicht
     * (art. 5 lid 2 AVG) vraagt om een reconstrueerbaar spoor van wie wat
     * wanneer heeft gedaan, en dat spoor moet juist blijven bestaan als de
     * onderliggende gegevens verdwijnen -- anders is niet meer aantoonbaar
     * dát er correct is opgeruimd. Dit botst met het recht op verwijdering
     * (art. 17), maar art. 17 lid 3 sub b/e maakt daarop een uitzondering voor
     * wettelijke verplichtingen en de instelling van rechtsvorderingen.
     *
     * In deze applicatie is dat bovendien geen keuze mét gevolgen voor de
     * database: het feitelijke auditlog loopt via minvws/laravel-logging naar
     * een logbestand/syslog, niet naar een tabel. De `audits`-tabel van
     * owen-it/laravel-auditing is leeg -- geen enkel model implementeert
     * `Auditable`. Er is dus geen auditrij die met een record mee zou worden
     * verwijderd, en er staan geen foreign keys op die tabel. De retentie van
     * het auditlog is daarmee een logbestand-vraagstuk, niet een
     * database-vraagstuk.
     *
     * @return array<class-string, int> aantal definitief verwijderde records per model
     */
    public function cleanupExpired(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $cutoff = $now->subDays(Config::integer('cleanup.retention_days'));
        $batchSize = Config::integer('cleanup.batch_size');

        $deleted = [];

        foreach (SoftDeleteCleanupOrder::models() as $modelClass) {
            $count = $this->cleanupModel($modelClass, $cutoff, $batchSize);

            if ($count === 0) {
                continue;
            }

            $deleted[$modelClass] = $count;
        }

        return $deleted;
    }

    /**
     * Verwijdert één record definitief, inclusief bijbehorende bestanden.
     *
     * Gebruikt door de beheerdersactie "definitief verwijderen", die de
     * bewaartermijn bewust overslaat (art. 17 AVG: een betrokkene hoeft geen
     * 90 dagen te wachten).
     */
    public function forceDeleteRecord(Model $record): void
    {
        Assert::true(
            in_array(SoftDeletes::class, class_uses_recursive($record::class), true),
            'Alleen soft-deletable modellen kunnen definitief verwijderd worden.',
        );

        DB::transaction(function () use ($record): void {
            $this->deleteMedia($record);

            $record->forceDelete();
        });
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function cleanupModel(string $modelClass, CarbonImmutable $cutoff, int $batchSize): int
    {
        $instance = new $modelClass();

        $deletedAtColumn = $this->deletedAtColumn($instance);

        /** @var Builder<Model> $query */
        $query = $instance->newQuery();
        $query->withoutGlobalScope(SoftDeletingScope::class)
            ->whereNotNull($deletedAtColumn)
            ->where($deletedAtColumn, '<', $cutoff);

        // Zelfverwijzende tabellen: diepste kinderen eerst, anders blokkeert de
        // parent_id-FK (NO ACTION) het verwijderen van een ouder.
        if (in_array($modelClass, SoftDeleteCleanupOrder::SELF_REFERENCING, true)) {
            $query->orderByRaw('parent_id IS NULL');
        }

        if ($batchSize > 0) {
            $query->limit($batchSize);
        }

        $count = 0;

        foreach ($query->get() as $record) {
            DB::transaction(function () use ($record): void {
                $this->deleteMedia($record);

                $record->forceDelete();
            });

            $count++;
        }

        return $count;
    }

    /**
     * Verwijdert de bijlagen van een record uit de documentopslag.
     *
     * Spatie's Media::delete() verwijdert het bestand van de disk. Staat de
     * applicatie op de lokale schijf (de standaard, `s3_enabled: false` in de
     * deploy-defaults), dan is dat een bestand op /storage; staat hij op
     * objectopslag, dan is het een object in de bucket. In beide gevallen is
     * het dezelfde aanroep -- de disk is een implementatiedetail van Flysystem.
     */
    private function deleteMedia(Model $record): void
    {
        if (!$record instanceof HasMedia) {
            return;
        }

        // Over alle collecties heen: deze modellen kennen meerdere mediagroepen
        // (bijlagen, posters, ...) en getMedia() zonder argument pakt er maar
        // één. Wat hier blijft staan, blijft als wees achter in de opslag.
        foreach (MediaGroup::cases() as $mediaGroup) {
            /** @var Media $media */
            foreach ($record->getMedia($mediaGroup->value) as $media) {
                $media->delete();
            }
        }
    }

    /**
     * De volledig gekwalificeerde naam van de soft-delete-kolom.
     *
     * getQualifiedDeletedAtColumn() komt uit Eloquents SoftDeletes-trait en is
     * op een generiek Model-type niet zichtbaar voor de statische analyse; de
     * modellen in de opschoonlijst gebruiken die trait allemaal, wat
     * SoftDeleteCleanerTest bewaakt.
     */
    private function deletedAtColumn(Model $model): string
    {
        Assert::methodExists($model, 'getQualifiedDeletedAtColumn');

        $column = $model->getQualifiedDeletedAtColumn();
        Assert::string($column);

        return $column;
    }
}
