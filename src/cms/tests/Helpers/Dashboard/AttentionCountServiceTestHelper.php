<?php

declare(strict_types=1);

namespace Tests\Helpers\Dashboard;

use App\Services\Dashboard\AttentionCountService;
use App\Services\Dashboard\DateWindow;

class AttentionCountServiceTestHelper
{
    public static function create(int $soonInMonths = DateWindow::DEFAULT_SOON_IN_MONTHS): AttentionCountService
    {
        return new AttentionCountService(new DateWindow($soonInMonths));
    }
}
