<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Widgets\FgRemarksWidget;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Principal;
use App\Services\AuthenticationService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('shows the form field', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(FgRemarksWidget::class, ['record' => $avgResponsibleProcessingRecord])
        ->assertSee(__('general.fg_remarks'));
});

it('cannot view without correct role', function (array $roles, bool $expectedResult): void {
    $this->mock(AuthenticationService::class)
        ->shouldReceive('principal')
        ->once()
        ->andReturn(new Principal($roles));

    expect(FgRemarksWidget::canView())
        ->toBe($expectedResult);
})->with([
    [[], false],
    [[Role::COUNSELOR], false],
    [[Role::DATA_PROTECTION_OFFICIAL], true],
    [[Role::COUNSELOR, Role::DATA_PROTECTION_OFFICIAL], true],
]);

it('handles submit', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    expect($avgResponsibleProcessingRecord->fgRemark)
        ->toBeNull();

    $body = fake()->sentence();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(FgRemarksWidget::class, ['record' => $avgResponsibleProcessingRecord])
        ->set('data', ['body' => $body])
        ->call('submit');

    $avgResponsibleProcessingRecord->refresh();

    expect($avgResponsibleProcessingRecord->fgRemark->body)
        ->toBe($body);
});
