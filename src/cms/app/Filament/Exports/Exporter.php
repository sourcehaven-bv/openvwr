<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Actions\Exports\ExportColumn;
use App\Filament\Resources\Resource;
use App\Models\DocumentType;
use App\Models\Organisation;
use App\Models\Scopes\TenantScope;
use Filament\Actions\Exports\ExportColumn as FilamentExportColumn;
use Filament\Actions\Exports\Exporter as FilamentExporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function __;
use function is_string;
use function number_format;
use function sprintf;
use function str_replace;

abstract class Exporter extends FilamentExporter
{
    /**
     * Builds the columns with the exported organisation as the active tenant.
     *
     * The columns are built twice: once in the request that starts the export, to
     * write the header, and again in the job that writes the rows. Only the first
     * of those runs inside a Filament request, so by the time the job rebuilds them
     * there is no tenant and the per-type columns would come back empty — leaving
     * the rows one cell short of the header for every type. The organisation is
     * carried over in the export options for that reason.
     *
     * @return array<FilamentExportColumn>
     */
    public function getCachedColumns(): array
    {
        $organisation = $this->getExportedOrganisation();

        if (!$organisation instanceof Organisation || Filament::getTenant() instanceof Organisation) {
            return parent::getCachedColumns();
        }

        Filament::setTenant($organisation, isQuiet: true);

        try {
            return parent::getCachedColumns();
        } finally {
            Filament::setTenant(null, isQuiet: true);
        }
    }

    /**
     * The organisation the export was started for, as recorded in its options.
     */
    private function getExportedOrganisation(): ?Organisation
    {
        $id = $this->options['organisation_id'] ?? null;

        if (!is_string($id)) {
            return null;
        }

        return Organisation::query()->find($id);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        /** @var class-string<Model> $model */
        $model = $export->exporter::$model;

        /** @var class-string<Resource> $resource */
        $resource = Filament::getModelResource($model);

        return __('export.notification.body', [
            'total_rows' => number_format($export->total_rows),
            'successful_rows' => number_format($export->successful_rows),
            'failed_rows' => number_format($export->getFailedRowsCount()),
            'model' => $resource::getPluralModelLabel(),
        ]);
    }

    /**
     * @template TModel of Model
     *
     * @param Builder<TModel> $query
     *
     * @return Builder<TModel>
     */
    public static function modifyQuery(Builder $query): Builder
    {
        return parent::modifyQuery($query)
            ->withGlobalScope('tenant', new TenantScope());
    }

    /**
     * The total, followed by one column per document type of the current organisation.
     *
     * The total stays because it is the only column that also counts documents
     * without a type, or of a type that has since been disabled. Without it the
     * per-type columns would not add up to the number of attached documents, and
     * in an audit that difference reads as an error rather than as a disabled type.
     *
     * The total is aliased explicitly rather than left to count('documents'). Laravel
     * keys the aggregates it adds by relation name, so the per-type counts — which
     * are aggregates on that same relation — would otherwise replace the total and
     * leave its cell empty.
     *
     * @return array<ExportColumn>
     */
    protected static function getDocumentColumns(): array
    {
        $columns = [
            ExportColumn::make('documents_count')
                ->label(__('document.model_plural'))
                ->counts(['documents as documents_count']),
        ];

        foreach (self::getDocumentTypes() as $id => $name) {
            $columns[] = self::getDocumentTypeColumn($id, $name);
        }

        return $columns;
    }

    /**
     * Counts the documents of a single type, as its own aggregate on the same relation.
     *
     * The alias is derived from the type's id rather than its name: a name is free
     * text that may hold spaces, quotes or a dot, and a dot would make Filament read
     * the alias as a relationship path instead of an attribute.
     */
    private static function getDocumentTypeColumn(string $id, string $name): ExportColumn
    {
        $alias = sprintf('document_type_%s_count', str_replace('-', '', $id));

        return ExportColumn::make($alias)
            ->label($name)
            ->counts([
                sprintf('documents as %s', $alias) => static function (Builder $query) use ($id): void {
                    $query->where('document_type_id', $id);
                },
            ]);
    }

    /**
     * The enabled document types of the current organisation, as id => name.
     *
     * Returns nothing when there is no tenant to resolve. That is the normal state
     * inside an export job: the worker restores the user but not the Filament
     * tenant, and the columns are rebuilt there to write the rows. Falling back to
     * the total alone would silently drop the per-type columns the header already
     * promised, so callers must set the tenant instead — getCachedColumns() does
     * that for the export jobs.
     *
     * Runs once per export per process — the columns are built once to write the
     * header and once more in the job — not once per exported row: the counts
     * themselves are aggregates on the export query, so the rows cost no queries.
     *
     * @return array<string, string>
     */
    private static function getDocumentTypes(): array
    {
        if (!Filament::getTenant() instanceof Organisation) {
            return [];
        }

        /** @var array<string, string> $types */
        $types = DocumentType::tenantQuery()
            ->where('enabled', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return $types;
    }
}
