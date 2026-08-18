<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Resources\DataBreachRecord\Pages\ApReportDataBreachRecord;
use App\Filament\Resources\DataBreachRecordResource;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Stakeholder;
use App\Services\ApReport\ApReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the AP preparation page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(DataBreachRecordResource::getUrl('ap-report', ['record' => $dataBreachRecord]))
        ->assertSuccessful();
});

it('denies access without the data breach view permission', function (): void {
    // The preparation exposes the full content of a breach record, so it must be
    // behind the same permission as the register itself.
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $user = UserTestHelper::createForOrganisationWithPermissions(
        $organisation,
        [Permission::CORE_ENTITY_VIEW],
    );

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ApReportDataBreachRecord::class, [
            'record' => $dataBreachRecord->getRouteKey(),
        ])
        ->assertForbidden();
});

it('shows the question numbers of the AP form so the online form can be followed', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(DataBreachRecordResource::getUrl('ap-report', ['record' => $dataBreachRecord]))
        ->assertSee('6.3.1')
        ->assertSee(__('ap_report.question.record_count'));
});

it('shows what the linked processing mentions as a pointer, naming its source', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create(['health' => true]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_special_categories' => null,
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $this->asFilamentOrganisationUser($organisation)
        ->get(DataBreachRecordResource::getUrl('ap-report', ['record' => $dataBreachRecord]))
        ->assertSee($processingRecord->name)
        ->assertSee(__('ap_report.hint_prefix'))
        ->assertSee(__('ap_report.hint_explanation'));
});

it('downloads the preparation as a PDF', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ApReportDataBreachRecord::class, [
            'record' => $dataBreachRecord->getRouteKey(),
        ])
        ->callAction('download_pdf')
        ->assertHasNoActionErrors();
});

it('renders a PDF that carries the structure of the AP form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'summary' => 'Persoonsgegevens onbedoeld gepubliceerd.',
    ]);

    $report = app(ApReportBuilder::class)->build($dataBreachRecord);
    $html = Blade::render('ap-report.pdf', ['report' => $report]);

    expect($html)->toContain(__('ap_report.chapter.breach'))
        ->and($html)->toContain('6.3.1')
        ->and($html)->toContain('Persoonsgegevens onbedoeld gepubliceerd.');

    $pdf = Pdf::loadHTML($html)->output();

    expect(substr($pdf, 0, 5))->toBe('%PDF-');
});
