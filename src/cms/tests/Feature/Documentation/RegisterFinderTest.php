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

it('refuses to return an empty list', function (): void {
    // A panel without registers means the navigation group was renamed or the
    // panel is misconfigured. Returning nothing would quietly yield a document
    // without content, so the finder says so instead. Every panel in this
    // application inherits the same resources, so the group name is pointed at
    // something no resource returns.
    $finder = new class extends RegisterFinder {
        protected function navigationGroups(): array
        {
            return ['A group nothing belongs to'];
        }
    };

    expect(fn () => $finder->find('admin'))
        ->toThrow(RuntimeException::class, 'No resources found');
});

it('accepts registers from more than one navigation group', function (): void {
    // An installation may split its registers over several menu groups - the
    // DPIA module has its own. Every listed group must be picked up.
    $finder = new class extends RegisterFinder {
        protected function navigationGroups(): array
        {
            return [__('navigation.registers'), __('navigation.management')];
        }
    };

    $registers = $finder->find('admin');

    expect($registers)
        ->toContain(AvgResponsibleProcessingRecordResource::class)
        ->toContain(DocumentResource::class);
});
