<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EntityNumberType;
use App\Models\EntityNumber;
use App\Models\EntityNumberCounter;
use App\Models\Organisation;

use function sprintf;

/**
 * Issues register and data-breach numbers from an organisation's counters.
 *
 * Shared by DemoSeeder and DemoRegisterSeeder: both create numbered entities,
 * and the counter has to advance across the two so numbers stay unique and
 * read as one continuous series (ZGN002, ZGN003, ...) in the overview tables.
 */
trait CreatesEntityNumbers
{
    private function createEntityNumber(Organisation $organisation, EntityNumberType $type): EntityNumber
    {
        $counter = $type === EntityNumberType::REGISTER
            ? $organisation->registerEntityNumberCounter
            : $organisation->databreachEntityNumberCounter;

        $this->advance($counter);

        return EntityNumber::factory()->create([
            'type' => $type,
            'number' => sprintf('%s%03d', $counter->prefix, $counter->number),
        ]);
    }

    private function advance(EntityNumberCounter $counter): void
    {
        $counter->number++;
        $counter->save();
    }
}
