<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function __;
use function array_filter;
use function array_values;
use function class_basename;
use function is_array;
use function sprintf;

/**
 * The fixed choices of a field, as the register's form offers them: a data
 * breach type is "Voorlopig" or "Definitief". They live next to the field's
 * label in resources/lang, under "<attribute>_options".
 */
class FieldOptions
{
    /**
     * @return array<int, string> empty when the field is free text
     */
    public function for(Model $model, string $attribute): array
    {
        $options = __(sprintf('%s.%s_options', Str::snake(class_basename($model)), $attribute));

        if (!is_array($options)) {
            return [];
        }

        return array_values(array_filter($options, 'is_string'));
    }

    /**
     * What the form starts a required choice out with: its first option.
     */
    public function default(Model $model, string $attribute): ?string
    {
        return $this->for($model, $attribute)[0] ?? null;
    }
}
