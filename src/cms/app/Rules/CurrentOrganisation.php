<?php

declare(strict_types=1);

namespace App\Rules;

use App\Facades\Authentication;
use Illuminate\Validation\Rules\Exists;

class CurrentOrganisation extends Exists
{
    public static function forModel(string $table, string $column = 'id'): Exists
    {
        $rule = new Exists($table, $column);
        $rule->where('organisation_id', Authentication::organisation()->id->toString());

        return $rule;
    }
}
