<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function array_map;
use function is_string;
use function is_subclass_of;
use function trim;

/**
 * A field that is cast to an enum takes a code ("primary") where a source
 * file writes the label ("Primair"). This is the bridge: the labels are what
 * the analyser and the review screen show, and a label read from a cell is
 * turned back into the case before it reaches the model.
 */
final class EnumField
{
    /**
     * @return class-string<BackedEnum>|null
     */
    public static function enumClass(Model $model, string $attribute): ?string
    {
        $cast = $model->getCasts()[$attribute] ?? null;

        return is_string($cast) && is_subclass_of($cast, BackedEnum::class) ? $cast : null;
    }

    /**
     * The choices as a person would write them; empty for a field that is not
     * an enum.
     *
     * @return array<int, string>
     */
    public static function labels(Model $model, string $attribute): array
    {
        $class = self::enumClass($model, $attribute);

        if ($class === null) {
            return [];
        }

        return array_map(static fn (BackedEnum $case): string => self::label($case), $class::cases());
    }

    /**
     * The case a cell value stands for, by label or by code, ignoring case.
     * Null when the field is not an enum or the value is not one of its choices.
     */
    public static function fromLabel(Model $model, string $attribute, string $value): ?BackedEnum
    {
        $class = self::enumClass($model, $attribute);

        if ($class === null) {
            return null;
        }

        $wanted = Str::lower(trim($value));

        foreach ($class::cases() as $case) {
            if (Str::lower(self::label($case)) === $wanted || Str::lower((string) $case->value) === $wanted) {
                return $case;
            }
        }

        return null;
    }

    /**
     * A fixed choice arrives as its label ("Primair"); the cast wants the case.
     * The dry-run has already refused anything that is not a choice.
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public static function casesFor(Model $model, array $attributes): array
    {
        foreach ($attributes as $attribute => $value) {
            if (is_string($value) && self::enumClass($model, $attribute) !== null) {
                $attributes[$attribute] = self::fromLabel($model, $attribute, $value);
            }
        }

        return $attributes;
    }

    private static function label(BackedEnum $case): string
    {
        if ($case instanceof HasLabel) {
            $label = $case->getLabel();

            if (is_string($label)) {
                return $label;
            }
        }

        return (string) $case->value;
    }
}
