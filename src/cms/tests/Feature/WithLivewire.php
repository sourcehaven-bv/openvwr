<?php

declare(strict_types=1);

namespace Tests\Feature;

use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

trait WithLivewire
{
    /**
     * @param array<string, mixed> $parameters Mount arguments for the component.
     * @param array<string, mixed> $queryParameters Query-string parameters, for components that
     *                                               hydrate #[Url] properties (which never bind
     *                                               from mount arguments in a real request).
     */
    final public function createLivewireTestable(string $componentName, array $parameters = [], array $queryParameters = []): Testable
    {
        if ($queryParameters !== []) {
            Livewire::withQueryParams($queryParameters);
        }

        return Livewire::test($componentName, $parameters);
    }
}
