<?php

declare(strict_types=1);

namespace App\Events\StaticWebsite;

use App\Events\LogDispatchable;

class AfterBuildEvent
{
    use LogDispatchable;

    public function __construct()
    {
    }
}
