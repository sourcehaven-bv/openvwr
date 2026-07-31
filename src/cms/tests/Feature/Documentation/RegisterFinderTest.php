<?php

declare(strict_types=1);

use App\Documentation\RegisterFinder;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\DocumentResource;

beforeEach(function (): void {
    $this->finder = new RegisterFinder();
});

it('finds the registers from the navigation group', function (): void {
    $registers = $this->finder->find('admin');

    expect($registers)->toContain(AvgResponsibleProcessingRecordResource::class);
});

it('leaves out resources from other navigation groups', function (): void {
    $registers = $this->finder->find('admin');

    // Documents sit under Management, not under Registers.
    expect($registers)->not->toContain(DocumentResource::class);
});

it('falls back to the default panel', function (): void {
    expect($this->finder->find())->toEqual($this->finder->find('admin'));
});

it('ignores an empty panel name', function (): void {
    expect($this->finder->find(''))->toEqual($this->finder->find('admin'));
});

it('orders the registers the way the menu does', function (): void {
    $registers = $this->finder->find('admin');

    $sorts = array_map(
        static fn (string $resource): int => $resource::getNavigationSort() ?? PHP_INT_MAX,
        $registers,
    );

    $sorted = $sorts;
    sort($sorted);

    expect($sorts)->toBe($sorted);
});

it('only returns resources that have their own form', function (): void {
    foreach ($this->finder->find('admin') as $resource) {
        $reflection = new ReflectionClass($resource);

        expect($reflection->getMethod('form')->getDeclaringClass()->getName())
            ->toBe($resource);
    }
});
