<?php

declare(strict_types=1);

namespace App\Documentation;

use App\Filament\NavigationGroups\NavigationGroup;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use ReflectionClass;
use RuntimeException;

use function __;
use function is_string;
use function sprintf;
use function usort;

use const PHP_INT_MAX;

/**
 * Finds which registers exist in this installation.
 *
 * The menu is the source: everything in the "Registers" navigation group counts.
 * That way the documentation generator needs no list of its own - whatever the
 * user sees as a register ends up in the document.
 */
class RegisterFinder
{
    /**
     * @return array<int, class-string<Resource>>
     */
    public function find(mixed $panelId = null): array
    {
        $panel = is_string($panelId) && $panelId !== ''
            ? Filament::getPanel($panelId)
            : Filament::getDefaultPanel();

        // Filament falls back to the default panel for an unknown id, so a typo
        // in --panel would silently document the wrong thing.
        if (is_string($panelId) && $panelId !== '' && $panel->getId() !== $panelId) {
            throw new RuntimeException(sprintf('Unknown Filament panel "%s".', $panelId));
        }

        $group = $this->navigationGroup();

        /** @var array<int, class-string<Resource>> $resources */
        $resources = $panel->getResources();

        $registers = [];

        foreach ($resources as $resourceClass) {
            if (!$this->isRegister($resourceClass, $group)) {
                continue;
            }

            $registers[] = $resourceClass;
        }

        // The same order as the menu.
        // No registers at all means the navigation group was renamed or the
        // panel is misconfigured. Returning an empty list would quietly produce
        // a document without any content, so say so instead.
        if ($registers === []) {
            throw new RuntimeException('No resources found in the "Registers" navigation group.');
        }

        usort($registers, static function (string $a, string $b): int {
            return ($a::getNavigationSort() ?? PHP_INT_MAX) <=> ($b::getNavigationSort() ?? PHP_INT_MAX);
        });

        return $registers;
    }

    /**
     * The navigation group that marks a resource as a register.
     */
    protected function navigationGroup(): string
    {
        $group = __(NavigationGroup::REGISTERS->value);

        return is_string($group) ? $group : NavigationGroup::REGISTERS->value;
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    private function isRegister(string $resourceClass, mixed $group): bool
    {
        if ($resourceClass::getNavigationGroup() !== $group) {
            return false;
        }

        return $this->hasOwnForm($resourceClass);
    }

    /**
     * Without its own form there is nothing to document; such a resource only
     * inherits Filament's empty default.
     *
     * @param class-string<Resource> $resourceClass
     */
    private function hasOwnForm(string $resourceClass): bool
    {
        $reflection = new ReflectionClass($resourceClass);

        return $reflection->getMethod('form')->getDeclaringClass()->getName() === $resourceClass;
    }
}
