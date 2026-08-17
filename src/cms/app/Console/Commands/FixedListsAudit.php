<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\FixedLists\Audit\FixedListAuditor;
use App\FixedLists\Audit\FixedListFinding;
use App\FixedLists\Audit\FixedListFindingType;
use Illuminate\Console\Command;

use function array_filter;
use function array_map;
use function array_values;
use function class_basename;
use function count;
use function sprintf;

class FixedListsAudit extends Command
{
    protected $signature = 'fixed-lists:audit
        {--type= : Only report findings of this type: retired, unknown, or unused. Unused values are left out unless asked for.}';
    protected $description = 'Report stored values that no longer match the fixed list they come from';

    public function handle(FixedListAuditor $fixedListAuditor): int
    {
        $type = $this->option('type');
        $filter = null;
        if ($type !== null) {
            $filter = FixedListFindingType::tryFrom($type);
            if ($filter === null) {
                $this->error(sprintf('Unknown finding type "%s".', $type));

                return self::FAILURE;
            }
        }

        $findings = $fixedListAuditor->audit();

        // Unused values are cleanup information rather than a compliance signal, and on a register that is
        // still filling up they would bury the findings that need action. Ask for them explicitly.
        $findings = array_values(array_filter(
            $findings,
            static function (FixedListFinding $finding) use ($filter): bool {
                if ($filter !== null) {
                    return $finding->type === $filter;
                }

                return $finding->type !== FixedListFindingType::UNUSED;
            },
        ));

        if (count($findings) === 0) {
            $this->info('All stored values match their fixed list.');

            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'Model', 'Column', 'Value', 'Records', 'Reason'],
            array_map(
                static fn (FixedListFinding $finding): array => [
                    $finding->type->value,
                    class_basename($finding->model),
                    $finding->column,
                    $finding->value,
                    (string) $finding->count,
                    $finding->reason ?? '',
                ],
                $findings,
            ),
        );

        return self::SUCCESS;
    }
}
