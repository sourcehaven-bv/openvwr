<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\QueryException;
use Throwable;

use function __;
use function is_string;
use function sprintf;

/**
 * Says why a row could not be written, in terms the user can act on and
 * without repeating the row itself.
 *
 * The database error names the problem precisely but quotes the offending
 * value, and the rows hold personal data. The SQLSTATE class is enough to tell
 * a value that is too long from one of the wrong kind, and that is what the
 * user needs in order to fix the sheet.
 */
final class WriteFailureReason
{
    /**
     * SQLSTATE => translation key under import_mapping.issue.
     */
    private const REASONS = [
        '22001' => 'write_failed_too_long',
        '22003' => 'write_failed_out_of_range',
        '22007' => 'write_failed_wrong_type',
        '22008' => 'write_failed_wrong_type',
        '22P02' => 'write_failed_wrong_type',
        '23502' => 'write_failed_required',
        '23503' => 'write_failed_reference',
        '23505' => 'write_failed_duplicate',
    ];

    public static function describe(Throwable $throwable): string
    {
        $key = 'write_failed';

        if ($throwable instanceof QueryException) {
            $state = $throwable->errorInfo[0] ?? null;

            if (is_string($state) && isset(self::REASONS[$state])) {
                $key = self::REASONS[$state];
            }
        }

        return __(sprintf('import_mapping.issue.%s', $key));
    }
}
