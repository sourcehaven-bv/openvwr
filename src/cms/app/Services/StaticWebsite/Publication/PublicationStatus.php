<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Publication;

use App\Models\Snapshot;
use App\Services\DateFormatService;
use Carbon\CarbonImmutable;

use function __;
use function sprintf;

/**
 * The publication state of one record, with the facts needed to explain it.
 */
final readonly class PublicationStatus
{
    /**
     * @param ?CarbonImmutable $date Online since (PUBLISHED, UPDATING), planned for (SCHEDULED) or last check (NOT_CONFIRMED)
     * @param ?int $onlineVersion Version visitors currently see
     * @param ?int $establishedVersion Latest established version
     * @param ?string $sectionTitle Website section that blocks publication (NOT_WEBSITE_SECTION_NOT_PUBLIC)
     * @param ?Snapshot $versionInProgress A version newer than the established one that is not established yet
     * @param ?PublicationStatus $blocker Why a REMOVING record will be taken offline
     */
    public function __construct(
        public PublicationState $state,
        public ?CarbonImmutable $date = null,
        public ?string $url = null,
        public ?int $onlineVersion = null,
        public ?int $establishedVersion = null,
        public ?string $sectionTitle = null,
        public ?Snapshot $versionInProgress = null,
        public ?PublicationStatus $blocker = null,
    ) {
    }

    public function getExplanation(): string
    {
        return __(sprintf('static_website.publication_state.%s.explanation', $this->state->value), [
            'date' => DateFormatService::toDateTime($this->date) ?? '',
            'online_version' => (string) $this->onlineVersion,
            'established_version' => (string) $this->establishedVersion,
            'section' => $this->sectionTitle ?? '',
            'reason' => $this->blocker?->getExplanation() ?? '',
        ]);
    }

    public function getVersionInProgressNotice(): ?string
    {
        if ($this->versionInProgress === null) {
            return null;
        }

        return __('static_website.publication_version_in_progress', [
            'version' => (string) $this->versionInProgress->version,
            'state' => __(sprintf('snapshot_state.label.%s', $this->versionInProgress->state::$name)),
        ]);
    }
}
