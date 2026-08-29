<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Models\Tag;

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(TagResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create an entry', function (): void {
    $name = fake()->word();

    $this->asFilamentUser()
        ->createLivewireTestable(CreateTag::class)
        ->fillForm([
            'name' => $name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Tag::class, [
        'name' => $name,
    ]);
});

it('can create an entry with a chosen colour', function (): void {
    $name = fake()->word();

    $this->asFilamentUser()
        ->createLivewireTestable(CreateTag::class)
        ->fillForm([
            'name' => $name,
            'color' => LabelColor::PURPLE->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Tag::class, [
        'name' => $name,
        'color' => LabelColor::PURPLE->value,
    ]);
});

it('falls back to an assigned colour when none is chosen', function (): void {
    $name = fake()->word();

    $this->asFilamentUser()
        ->createLivewireTestable(CreateTag::class)
        ->fillForm([
            'name' => $name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tag = Tag::query()->where('name', $name)->sole();

    expect($tag->color)->toBeInstanceOf(LabelColor::class);
});
