<?php

declare(strict_types=1);

namespace App\FixedLists\Audit;

use App\FixedLists\FixedListColumn;
use App\FixedLists\FixedListRegistry;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function array_key_exists;

class FixedListAuditor
{
    public function __construct(
        private readonly FixedListRegistry $fixedListRegistry,
    ) {
    }

    /**
     * @return list<FixedListFinding>
     */
    public function audit(): array
    {
        $findings = [];
        foreach ($this->fixedListRegistry->columns() as $fixedListColumn) {
            foreach ($this->auditColumn($fixedListColumn) as $finding) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    /**
     * @param FixedListColumn<covariant Model> $fixedListColumn
     *
     * @return list<FixedListFinding>
     */
    private function auditColumn(FixedListColumn $fixedListColumn): array
    {
        $counts = $this->countStoredValues($fixedListColumn);

        $findings = [];
        foreach ($counts as $value => $count) {
            $entry = $fixedListColumn->list->find($value);
            if ($entry === null) {
                $findings[] = new FixedListFinding(
                    $fixedListColumn->model,
                    $fixedListColumn->column,
                    $value,
                    FixedListFindingType::UNKNOWN,
                    $count,
                );

                continue;
            }

            if (!$entry->isRetired()) {
                continue;
            }

            $findings[] = new FixedListFinding(
                $fixedListColumn->model,
                $fixedListColumn->column,
                $value,
                FixedListFindingType::RETIRED,
                $count,
                $entry->retiredReason,
            );
        }

        foreach ($fixedListColumn->list->allValues() as $value) {
            if (array_key_exists($value, $counts)) {
                continue;
            }

            $findings[] = new FixedListFinding($fixedListColumn->model, $fixedListColumn->column, $value, FixedListFindingType::UNUSED, 0);
        }

        return $findings;
    }

    /**
     * Counts the non-empty stored values of the column, soft deleted records included: those can be restored,
     * so their values still matter.
     *
     * @param FixedListColumn<covariant Model> $fixedListColumn
     *
     * @return array<string, int>
     */
    private function countStoredValues(FixedListColumn $fixedListColumn): array
    {
        $model = new $fixedListColumn->model();

        // The audit is an organisation-wide sweep, so it drops every global scope: tenant scoping would hide
        // other organisations' values, a default ordering conflicts with the grouping, and soft deleted
        // records still count because they can be restored.
        $query = $model->newQuery()->withoutGlobalScopes();

        $column = $fixedListColumn->column;

        $rows = $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->select($column)
            ->selectRaw('count(*) as total')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $value = $row->getAttribute($column);
            Assert::string($value);

            $total = $row->getAttribute('total');
            Assert::integerish($total);

            $counts[$value] = (int) $total;
        }

        return $counts;
    }
}
