<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property bool $enabled
 */
abstract class LookupListModel extends Model implements TenantAware
{
    use HasUuidAsId;
    use HasOrganisation;
    use HasTimestamps;

    protected $fillable = [
        'name',
        'enabled',
    ];
}
