<?php

declare(strict_types=1);

namespace App\Documentation;

use App\Filament\NavigationGroups\NavigationGroup;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use ReflectionClass;
use Throwable;

use function __;
use function is_string;
use function is_subclass_of;
use function usort;

use const PHP_INT_MAX;

/**
 * Zoekt op welke registers er in deze installatie zijn.
 *
 * De bron is het menu: alles wat in de navigatiegroep "Registers" staat telt
 * mee. Daardoor hoeft de documentatiegenerator geen lijst met registers te
 * kennen - wat de gebruiker als register ziet, komt in het document.
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

        $group = __(NavigationGroup::REGISTERS->value);

        $registers = [];

        foreach ($panel->getResources() as $resourceClass) {
            if (!is_subclass_of($resourceClass, Resource::class)) {
                continue;
            }

            if (!$this->isRegister($resourceClass, $group)) {
                continue;
            }

            $registers[] = $resourceClass;
        }

        // Dezelfde volgorde als in het menu.
        usort($registers, static function (string $a, string $b): int {
            return ($a::getNavigationSort() ?? PHP_INT_MAX) <=> ($b::getNavigationSort() ?? PHP_INT_MAX);
        });

        return $registers;
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    private function isRegister(string $resourceClass, mixed $group): bool
    {
        try {
            if ($resourceClass::getNavigationGroup() !== $group) {
                return false;
            }
        } catch (Throwable) {
            return false;
        }

        return $this->hasOwnForm($resourceClass);
    }

    /**
     * Zonder eigen formulier valt er niets te documenteren; zo'n resource erft
     * alleen de lege standaard van Filament.
     *
     * @param class-string<Resource> $resourceClass
     */
    private function hasOwnForm(string $resourceClass): bool
    {
        $reflection = new ReflectionClass($resourceClass);

        if (!$reflection->hasMethod('form')) {
            return false;
        }

        return $reflection->getMethod('form')->getDeclaringClass()->getName() === $resourceClass;
    }
}
