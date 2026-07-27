<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\Resource;
use App\Models\Document;
use App\Models\DocumentType;
use App\ValueObjects\CalendarDate;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function __;

/**
 * One row of the "wat is er verlopen" list: a register item past its review
 * date, or a document past its expiry.
 *
 * The two are different models with different date columns and different
 * owning resources, but the same thing to a user — something whose date has
 * passed — so they are flattened into one comparable shape here rather than
 * being kept apart all the way into the view.
 */
final readonly class OverdueItem
{
    /**
     * The two sources store their date differently — review_at is a
     * CalendarDate, documents.expires_at a plain date cast — so the row holds a
     * CarbonImmutable and each factory normalises into it. Keeping both types
     * here would push that difference into every consumer.
     */
    private function __construct(
        public string $name,
        public CarbonImmutable $date,
        public string $type,
        public string $kind,
        public string $url,
    ) {
    }

    /**
     * @param class-string<Resource> $filamentResource
     */
    public static function forReview(Model $record, string $filamentResource, string $type): self
    {
        $reviewAt = $record->getAttribute('review_at');
        Assert::isInstanceOf($reviewAt, CalendarDate::class);

        $name = $record->getAttribute('name');
        Assert::string($name);

        return new self(
            $name,
            CarbonImmutable::parse((string) $reviewAt),
            $type,
            __('general.review_at'),
            $filamentResource::getUrl('edit', ['record' => $record]),
        );
    }

    public static function forDocument(Document $document): self
    {
        $expiresAt = $document->getAttribute('expires_at');
        Assert::isInstanceOf($expiresAt, CarbonInterface::class);

        // The document's own type ("DPIA", "Verwerkersovereenkomst") rather than
        // the generic model label: it is what distinguishes one document from
        // another in a mixed list. Read off the relation rather than through
        // ->documentType, whose generic claims a DocumentType is always present
        // while document_type_id is nullable and the form does not require it.
        $documentType = $document->getAttribute('documentType');
        $type = $documentType instanceof DocumentType
            ? $documentType->name
            : __('document.model_singular');

        return new self(
            $document->name,
            CarbonImmutable::instance($expiresAt),
            $type,
            __('document.expires_at'),
            DocumentResource::getUrl('edit', ['record' => $document]),
        );
    }
}
