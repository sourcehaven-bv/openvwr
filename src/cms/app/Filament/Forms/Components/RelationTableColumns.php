<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Media\MediaGroup;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\DateFormatService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

use function __;
use function is_string;
use function sprintf;

/**
 * Central definition of the table columns each linked model shows inside a
 * {@see RelationTable}. Keeping the column sets here (rather than repeated at
 * every call site) keeps the ~20 usages a single line and the column logic in
 * one place.
 */
class RelationTableColumns
{
    /**
     * @param class-string<Model> $model
     *
     * @return array<int, array{label: string, get: Closure(Model): (string|null), href?: Closure(Model): (string|null)}>
     */
    public static function for(string $model): array
    {
        return match ($model) {
            Document::class => self::documents(),
            Responsible::class => self::responsibles(),
            Processor::class => self::processors(),
            Receiver::class => self::simpleDescription(),
            System::class => self::simpleDescription(),
            ContactPerson::class => self::contactPersons(),
            AvgResponsibleProcessingRecord::class,
            AvgProcessorProcessingRecord::class,
            WpgProcessingRecord::class => self::processingRecords(),
            AlgorithmRecord::class => self::algorithmRecords(),
            DataBreachRecord::class => self::dataBreachRecords(),
            default => throw new InvalidArgumentException(sprintf('No RelationTable columns defined for model [%s]', $model)),
        };
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null), href?: Closure(Model): (string|null)}>
     */
    private static function documents(): array
    {
        return [
            [
                'label' => __('document.name'),
                'get' => static fn (Model $record): string => self::as($record, Document::class)->name,
                // When the document has an uploaded file, its name links to the download.
                'href' => static fn (Model $record): ?string => self::as($record, Document::class)
                    ->getFirstMedia(MediaGroup::ATTACHMENTS->value)?->getFullUrl(),
            ],
            [
                'label' => __('document.expires_at'),
                'get' => static fn (Model $record): ?string => DateFormatService::toDate(self::as($record, Document::class)->expires_at),
            ],
            [
                'label' => __('document.notify_at'),
                'get' => static fn (Model $record): ?string => DateFormatService::toDate(self::as($record, Document::class)->notify_at),
            ],
            [
                'label' => __('document.type'),
                'get' => static fn (Model $record): ?string => self::as($record, Document::class)->documentType?->name,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function responsibles(): array
    {
        return [
            [
                'label' => __('responsible.name'),
                'get' => static fn (Model $record): string => self::as($record, Responsible::class)->name,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function processors(): array
    {
        return [
            [
                'label' => __('processor.name'),
                'get' => static fn (Model $record): string => self::as($record, Processor::class)->name,
            ],
            [
                'label' => __('processor.email'),
                'get' => static fn (Model $record): string => self::as($record, Processor::class)->email,
            ],
            [
                'label' => __('processor.phone'),
                'get' => static fn (Model $record): string => self::as($record, Processor::class)->phone,
            ],
        ];
    }

    /**
     * Receiver and System are both titled by their `description` attribute.
     *
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function simpleDescription(): array
    {
        return [
            [
                'label' => __('general.description'),
                'get' => static function (Model $record): ?string {
                    $description = $record->getAttribute('description');

                    return is_string($description) ? $description : null;
                },
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function contactPersons(): array
    {
        return [
            [
                'label' => __('general.name'),
                'get' => static fn (Model $record): string => self::as($record, ContactPerson::class)->name,
            ],
            [
                'label' => __('contact_person_position.model_singular'),
                'get' => static fn (Model $record): ?string => self::as($record, ContactPerson::class)->contactPersonPosition?->name,
            ],
            [
                'label' => __('contact_person.email'),
                'get' => static fn (Model $record): ?string => self::as($record, ContactPerson::class)->email,
            ],
            [
                'label' => __('contact_person.phone'),
                'get' => static fn (Model $record): ?string => self::as($record, ContactPerson::class)->phone,
            ],
        ];
    }

    /**
     * The AVG/WPG processing records share the same shape (number + name).
     *
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function processingRecords(): array
    {
        return [
            [
                'label' => __('processing_record.number'),
                'get' => static fn (Model $record): ?string => self::entityNumber($record),
            ],
            [
                'label' => __('general.name'),
                'get' => static function (Model $record): ?string {
                    $name = $record->getAttribute('name');

                    return is_string($name) ? $name : null;
                },
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function algorithmRecords(): array
    {
        return [
            [
                'label' => __('processing_record.number'),
                'get' => static fn (Model $record): ?string => self::entityNumber($record),
            ],
            [
                'label' => __('general.name'),
                'get' => static fn (Model $record): string => self::as($record, AlgorithmRecord::class)->name,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null)}>
     */
    private static function dataBreachRecords(): array
    {
        return [
            [
                'label' => __('processing_record.number'),
                'get' => static fn (Model $record): ?string => self::entityNumber($record),
            ],
            [
                'label' => __('general.name'),
                'get' => static fn (Model $record): string => self::as($record, DataBreachRecord::class)->name,
            ],
            [
                'label' => __('data_breach_record.reported_at'),
                'get' => static fn (Model $record): ?string => DateFormatService::toDate(
                    self::as($record, DataBreachRecord::class)->reported_at,
                ),
            ],
        ];
    }

    /**
     * Resolves the `entityNumber` relation's number for records that use it,
     * without needing a per-model cast.
     */
    private static function entityNumber(Model $record): ?string
    {
        $entityNumber = $record->getAttribute('entityNumber');

        if ($entityNumber instanceof Model) {
            $number = $entityNumber->getAttribute('number');

            return is_string($number) ? $number : null;
        }

        return null;
    }

    /**
     * Narrows a linked record to its concrete model so column closures get
     * typed property access. The relationship guarantees the type; a mismatch
     * would be a programming error.
     *
     * @template T of Model
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private static function as(Model $record, string $class): Model
    {
        if ($record instanceof $class) {
            return $record;
        }

        // @codeCoverageIgnoreStart
        throw new InvalidArgumentException(sprintf('Expected a [%s], got [%s]', $class, $record::class));
        // @codeCoverageIgnoreEnd
    }
}
