<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function array_map;
use function array_unique;
use function array_values;
use function hash;
use function implode;
use function is_array;
use function preg_replace;
use function sort;
use function trim;

/**
 * The declarative model in the middle: which source column feeds which target
 * field, and how its value is converted.
 *
 * Snapshots and workflow states cannot be expressed here; see
 * docs/import_mapping_design.md.
 *
 * @template TModel of Model
 */
readonly class MappingProfile
{
    /**
     * @param class-string<TModel> $target
     * @param array<int, MappingField> $fields
     * @param array<int, string> $unmapped columns deliberately left out
     */
    public function __construct(
        public string $target,
        public array $fields,
        public array $unmapped = [],
        public ?string $identity = null,
    ) {
    }

    /**
     * A stable signature of the source layout, used to recognise a sheet that
     * has been imported before.
     *
     * @param array<int, string> $headers
     */
    public static function fingerprint(array $headers): string
    {
        // Casing and spacing vary between exports of the same layout and do
        // not make it a different layout.
        $normalised = array_map(
            static fn (string $header): string => Str::lower(trim(preg_replace('/\s+/u', ' ', $header) ?? $header)),
            $headers,
        );
        $normalised = array_values(array_unique($normalised));
        sort($normalised);

        return hash('sha256', implode("\n", $normalised));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self<Model>
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'target');
        Assert::string($data['target']);
        /** @var class-string<Model> $target */
        $target = $data['target'];

        $fields = [];
        if (isset($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field) {
                Assert::isMap($field);
                $fields[] = MappingField::fromArray($field);
            }
        }

        $unmapped = [];
        if (isset($data['unmapped']) && is_array($data['unmapped'])) {
            Assert::allString($data['unmapped']);
            $unmapped = array_values($data['unmapped']);
        }

        $identity = null;
        if (isset($data['identity'])) {
            Assert::string($data['identity']);
            $identity = $data['identity'];
        }

        return new self($target, $fields, $unmapped, $identity);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'target' => $this->target,
            'identity' => $this->identity,
            'fields' => array_map(
                static fn (MappingField $field): array => $field->toArray(),
                $this->fields,
            ),
            'unmapped' => $this->unmapped,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function mappedSources(): array
    {
        return array_map(
            static fn (MappingField $field): string => $field->source,
            $this->fields,
        );
    }
}
