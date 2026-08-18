<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages;

use App\Filament\Resources\DataBreachRecordResource;
use App\Models\DataBreachRecord;
use App\Services\ApReport\ApReport;
use App\Services\ApReport\ApReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function response;
use function sprintf;

/**
 * Shows the AP notification form preparation on screen, so answers can be copied
 * straight into the online form, and offers the same content as a PDF.
 */
class ApReportDataBreachRecord extends Page
{
    use InteractsWithRecord;

    protected static string $resource = DataBreachRecordResource::class;
    protected static string $view = 'filament.pages.ap-report';

    public function mount(string|int $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
    }

    public function getTitle(): string|Htmlable
    {
        return __('ap_report.title');
    }

    public function getHeading(): string|Htmlable
    {
        return sprintf('%s (%s)', $this->getDataBreachRecord()->name, $this->getDataBreachRecord()->getNumber());
    }

    public function getReport(): ApReport
    {
        $dataBreachRecord = $this->getDataBreachRecord();

        // The preparation walks the whole graph around the breach; loading it up
        // front keeps that to a handful of queries.
        $dataBreachRecord->loadMissing([
            'documents',
            'organisation.responsibleLegalEntity',
            'responsibles.address',
            'avgResponsibleProcessingRecords.processors',
            'avgResponsibleProcessingRecords.receivers',
            'avgResponsibleProcessingRecords.stakeholders',
            'avgProcessorProcessingRecords.processors',
            'avgProcessorProcessingRecords.stakeholders',
            'wpgProcessingRecords',
        ]);

        return app(ApReportBuilder::class)->build($dataBreachRecord);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label(__('ap_report.action_pdf_label'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => $this->downloadPdf()),
        ];
    }

    protected function authorizeAccess(): void
    {
        $this->authorize('view', $this->getDataBreachRecord());
    }

    private function downloadPdf(): StreamedResponse
    {
        $report = $this->getReport();

        $filename = Str::of($this->getDataBreachRecord()->getNumber())
            ->append('-ap-melding')
            ->slug()
            ->append('.pdf')
            ->toString();

        return response()->streamDownload(static function () use ($report): void {
            $html = Blade::render('ap-report.pdf', ['report' => $report]);

            echo Pdf::loadHTML($html)->stream();
        }, $filename);
    }

    private function getDataBreachRecord(): DataBreachRecord
    {
        $dataBreachRecord = $this->getRecord();
        Assert::isInstanceOf($dataBreachRecord, DataBreachRecord::class);

        return $dataBreachRecord;
    }
}
