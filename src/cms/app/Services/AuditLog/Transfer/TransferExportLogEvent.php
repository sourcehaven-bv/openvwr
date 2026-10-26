<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Transfer;

use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;

final class TransferExportLogEvent extends GeneralLogEvent
{
    public const EVENT_CODE = '093337';
    public const EVENT_KEY = 'transfer_export';
}
