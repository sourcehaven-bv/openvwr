<?php

declare(strict_types=1);

namespace App\Filament\NavigationGroups;

enum NavigationGroup: string
{
    case REGISTERS = 'navigation.registers';
    case DPIA = 'navigation.dpia';
    case MANAGEMENT = 'navigation.management';
    case PROCESSORS = 'navigation.processors';
    case FUNCTIONAL_MANAGEMENT = 'navigation.functional_management';
    case LOOKUP_LISTS = 'navigation.lookup_lists';
    case ORGANISATION = 'navigation.organisation';
    case OVERVIEWS = 'navigation.overviews';
}
