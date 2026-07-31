<?php

declare(strict_types=1);

namespace App\Documentation;

use App\Filament\NavigationGroups\NavigationGroup;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use ReflectionClass;
use RuntimeException;

use function __;
use function array_search;
use function in_array;
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

        $groups = $this->navigationGroups();

        /** @var array<int, class-string<Resource>> $resources */
        $resources = $panel->getResources();

        $registers = [];

        foreach ($resources as $resourceClass) {
            if (!$this->isRegister($resourceClass, $groups)) {
                continue;
            }

            $registers[] = $resourceClass;
        }

        // No registers at all means the navigation group was renamed or the
        // panel is misconfigured. Returning an empty list would quietly produce
        // a document without any content, so say so instead.
        if ($registers === []) {
            throw new RuntimeException('No resources found in the "Registers" navigation group.');
        }

        // The same order as the menu: group by group, and within a group by
        // navigation sort. Sort numbers restart per group, so ordering on them
        // alone would interleave the groups.
        usort($registers, static function (string $a, string $b) use ($groups): int {
            return [
                array_search($a::getNavigationGroup(), $groups, true),
                $a::getNavigationSort() ?? PHP_INT_MAX,
            ] <=> [
                array_search($b::getNavigationGroup(), $groups, true),
                $b::getNavigationSort() ?? PHP_INT_MAX,
            ];
        });

        return $registers;
    }

    /**
     * The navigation groups whose resources count as a register.
     *
     * An installation may split its registers over more than one menu group -
     * the DPIA module has its own, for instance - so every group listed here
     * ends up in the document.
     *
     * @return array<int, string>
     */
    protected function navigationGroups(): array
    {
        // The DPIA module is not present in every installation, so its group is
        // named rather than referenced through the enum.
        $keys = [NavigationGroup::REGISTERS->value, 'navigation.dpia'];

        $groups = [];

        foreach ($keys as $key) {
            $label = __($key);
            $groups[] = is_string($label) ? $label : $key;
        }

        return $groups;
    }

    /**
     * @param class-string<Resource> $resourceClass
     * @param array<int, string> $groups
     */
    private function isRegister(string $resourceClass, array $groups): bool
    {
        if (!in_array($resourceClass::getNavigationGroup(), $groups, true)) {
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
