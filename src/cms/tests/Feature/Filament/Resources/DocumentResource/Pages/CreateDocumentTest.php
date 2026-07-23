<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Models\Document;
use App\Models\DocumentType;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(DocumentResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create an entry', function (): void {
    $name = fake()->word();

    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => $name,
            'document_type_id' => $documentType->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Document::class, [
        'name' => $name,
        'document_type_id' => $documentType->id,
    ]);
});

it('can use the notify_at_expires_at action', function (): void {
    $name = fake()->word();
    $expiresAt = CarbonImmutable::instance(fake()->dateTime());

    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => $name,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'document_type_id' => $documentType->id,
        ])
        ->mountFormComponentAction('notify_at', 'notify_at_expires_at')
        ->callFormComponentAction('notify_at', 'notify_at_expires_at')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Document::class, [
        'name' => $name,
        'expires_at' => $expiresAt->format('Y-m-d'),
        'notify_at' => $expiresAt->format('Y-m-d'),
    ]);
});

it('hides the notify_at_expires_at action if expires_at not set', function (): void {
    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => fake()->word(),
            'expires_at' => null,
            'document_type_id' => $documentType->id,
        ])
        ->assertFormComponentActionHidden('notify_at', 'notify_at_expires_at');
});

it('can use the notify_at_1_month_before action', function (): void {
    $name = fake()->word();
    $expiresAt = CarbonImmutable::instance(fake()->dateTime());

    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => $name,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'document_type_id' => $documentType->id,
        ])
        ->mountFormComponentAction('notify_at', 'notify_at_1_month_before')
        ->callFormComponentAction('notify_at', 'notify_at_1_month_before')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Document::class, [
        'name' => $name,
        'expires_at' => $expiresAt->format('Y-m-d'),
        'notify_at' => $expiresAt->subMonth()->format('Y-m-d'),
    ]);
});

it('hides the notify_at_1_month_before action if expires_at not set', function (): void {
    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => fake()->word(),
            'expires_at' => null,
            'document_type_id' => $documentType->id,
        ])
        ->assertFormComponentActionHidden('notify_at', 'notify_at_1_month_before');
});

it('can use the notify_at_3_months_before action', function (): void {
    $name = fake()->word();
    $expiresAt = CarbonImmutable::instance(fake()->dateTime());

    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => $name,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'document_type_id' => $documentType->id,
        ])
        ->mountFormComponentAction('notify_at', 'notify_at_3_months_before')
        ->callFormComponentAction('notify_at', 'notify_at_3_months_before')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Document::class, [
        'name' => $name,
        'expires_at' => $expiresAt->format('Y-m-d'),
        'notify_at' => $expiresAt->subMonths(3)->format('Y-m-d'),
    ]);
});

it('hides the notify_at_3_months_before action if expires_at not set', function (): void {
    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateDocument::class)
        ->fillForm([
            'name' => fake()->word(),
            'expires_at' => null,
            'document_type_id' => $documentType->id,
        ])
        ->assertFormComponentActionHidden('notify_at', 'notify_at_3_months_before');
});
