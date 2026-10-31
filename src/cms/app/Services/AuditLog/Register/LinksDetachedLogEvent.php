<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Register;

use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;

final class LinksDetachedLogEvent extends GeneralLogEvent
{
    public const EVENT_CODE = '093339';
    public const EVENT_KEY = 'links_detached';
}
