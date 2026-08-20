<?php

declare(strict_types=1);

namespace App\FixedLists;

use function array_key_exists;
use function array_map;
use function array_values;

/**
 * A list of allowed values that is fixed in code rather than stored per organisation.
 *
 * Use this for values that follow from an external source (law, standard, ministerial policy) and are
 * therefore the same for every organisation. Values that organisations maintain themselves belong in a
 * lookup list (see App\Models\LookupListModel) instead.
 */
abstract class FixedList
{
    /** @var array<string, FixedListEntry>|null */
    private ?array $entriesByValue = null;

    /**
     * @return list<FixedListEntry>
     */
    abstract protected function entries(): array;

    /**
     * Values that may be selected for new input.
     *
     * @return list<string>
     */
    public function currentValues(): array
    {
        $currentEntries = [];
        foreach ($this->entriesByValue() as $entry) {
            if ($entry->isRetired()) {
                continue;
            }

            $currentEntries[] = $entry->value;
        }

        return $currentEntries;
    }

    /**
     * Every value the list has ever contained, including retired ones.
     *
     * @return list<string>
     */
    public function allValues(): array
    {
        return array_map(
            static fn (FixedListEntry $entry): string => $entry->value,
            array_values($this->entriesByValue()),
        );
    }

    public function find(string $value): ?FixedListEntry
    {
        $entriesByValue = $this->entriesByValue();

        if (!array_key_exists($value, $entriesByValue)) {
            return null;
        }

        return $entriesByValue[$value];
    }

    public function isCurrent(string $value): bool
    {
        $entry = $this->find($value);

        return $entry !== null && !$entry->isRetired();
    }

    public function isRetired(string $value): bool
    {
        $entry = $this->find($value);

        return $entry !== null && $entry->isRetired();
    }

    public function isKnown(string $value): bool
    {
        return $this->find($value) !== null;
    }

    /**
     * @return array<string, FixedListEntry>
     */
    private function entriesByValue(): array
    {
        if ($this->entriesByValue === null) {
            $entriesByValue = [];
            foreach ($this->entries() as $entry) {
                $entriesByValue[$entry->value] = $entry;
            }

            $this->entriesByValue = $entriesByValue;
        }

        return $this->entriesByValue;
    }
}
