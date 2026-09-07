<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function is_string;

/**
 * A single source-column to target-field mapping.
 */
readonly class MappingField
{
    public function __construct(
        public string $source,
        public string $target,
        public MappingTransform $transform,
        public MappingConfidence $confidence,
        /**
         * For BooleanToDate: the date a "yes" stands for. Null means the date of
         * the import itself.
         */
        public ?string $trueDate = null,
        /**
         * Set when the target is a shared entity rather than a plain column;
         * holds the RelationTarget key.
         */
        public ?string $relation = null,
        /**
         * Splits one cell into several entities, e.g. "Firma A\nFirma B".
         *
         * @var non-empty-string
         */
        public string $separator = "\n",
        /**
         * For Date: how the source writes its dates, e.g. "d-m-Y". Decided per
         * column, because "04-03-2026" cannot be read on its own.
         */
        public ?string $dateFormat = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'source');
        Assert::keyExists($data, 'target');
        Assert::string($data['source']);
        Assert::string($data['target']);

        $transform = array_key_exists('transform', $data) && is_string($data['transform'])
            ? MappingTransform::from($data['transform'])
            : MappingTransform::Text;

        $confidence = array_key_exists('confidence', $data) && is_string($data['confidence'])
            ? MappingConfidence::from($data['confidence'])
            : MappingConfidence::Manual;

        $trueDate = null;
        if (array_key_exists('true_date', $data) && is_string($data['true_date'])) {
            $trueDate = $data['true_date'];
        }

        $relation = null;
        if (array_key_exists('relation', $data) && is_string($data['relation'])) {
            $relation = $data['relation'];
        }

        $dateFormat = null;
        if (array_key_exists('date_format', $data) && is_string($data['date_format'])) {
            $dateFormat = $data['date_format'];
        }

        return new self($data['source'], $data['target'], $transform, $confidence, $trueDate, $relation, dateFormat: $dateFormat);
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'target' => $this->target,
            'transform' => $this->transform->value,
            'confidence' => $this->confidence->value,
            'true_date' => $this->trueDate,
            'relation' => $this->relation,
            'date_format' => $this->dateFormat,
        ];
    }
}
