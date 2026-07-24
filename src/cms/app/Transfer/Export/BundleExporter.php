<?php

declare(strict_types=1);

namespace App\Transfer\Export;

use App\Models\Document;
use App\Models\Organisation;
use App\Transfer\TransferEntityType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Webmozart\Assert\Assert;
use ZipArchive;

use function array_values;
use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class BundleExporter
{
    public const int FORMAT_VERSION = 1;
    public const string FORMAT_NAME = 'openvwr-transfer';
    public const string DISK = 'filament';
    public const string EXPORT_DIRECTORY = 'transfer/exports';

    public function __construct(
        private readonly EntityGraphCollector $entityGraphCollector,
        private readonly EntitySerializer $entitySerializer,
    ) {
    }

    /**
     * Build a transfer zip for the given records and return its path on the export disk.
     *
     * @param list<string> $recordIds
     * @param array<string, list<string>> $selectedRelated selected related ids, keyed by relation name
     */
    public function export(
        TransferEntityType $recordType,
        array $recordIds,
        array $selectedRelated,
        Organisation $organisation,
    ): string {
        $records = $recordType->modelClass()::query()
            ->whereBelongsTo($organisation)
            ->whereIn('id', $recordIds)
            ->get();

        $entities = $this->entityGraphCollector->collect(array_values($records->all()), $selectedRelated);

        return $this->writeZip($entities, $organisation);
    }

    /**
     * @param array<string, Model> $entities
     */
    private function writeZip(array $entities, Organisation $organisation): string
    {
        $disk = Storage::disk(self::DISK);
        $relativePath = sprintf(
            '%s/openvwr-export-%s.zip',
            self::EXPORT_DIRECTORY,
            CarbonImmutable::now()->format('Ymd-His-v'),
        );

        File::ensureDirectoryExists($disk->path(self::EXPORT_DIRECTORY));

        $zip = new ZipArchive();
        $openResult = $zip->open($disk->path($relativePath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
        Assert::same($openResult, true, sprintf('could not create zip at %s', $relativePath));

        $manifestEntities = [];

        foreach ($entities as $id => $model) {
            $type = TransferEntityType::fromModel($model);
            $data = $this->entitySerializer->serialize($model);

            $zip->addFromString(
                sprintf('entities/%s/%s.json', $type->value, $id),
                $this->toJson($data),
            );

            if ($model instanceof Document) {
                foreach ($model->media as $mediaItem) {
                    $zip->addFile($mediaItem->getPath(), sprintf('media/%s/%s', $mediaItem->uuid, $mediaItem->file_name));
                }
            }

            $manifestEntities[] = [
                'type' => $type->value,
                'id' => $id,
                'origin_id' => $data['origin_id'],
                'name' => $data['name'],
            ];
        }

        $manifest = [
            'format' => self::FORMAT_NAME,
            'version' => self::FORMAT_VERSION,
            'exported_at' => CarbonImmutable::now()->toIso8601String(),
            'source_organisation' => [
                'id' => $organisation->id->toString(),
                'name' => $organisation->name,
            ],
            'entities' => $manifestEntities,
        ];

        $zip->addFromString('manifest.json', $this->toJson($manifest));
        $zip->close();

        return $relativePath;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function toJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
