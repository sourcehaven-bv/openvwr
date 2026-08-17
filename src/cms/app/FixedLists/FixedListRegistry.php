<?php

declare(strict_types=1);

namespace App\FixedLists;

use App\FixedLists\Lists\AdequacyDecisionCountryList;
use App\FixedLists\Lists\TransferMechanismList;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Declares which columns are governed by which fixed list.
 *
 * Add an entry here when a column is filled from a fixed list, so that the stored values are covered by
 * `fixed-lists:audit`.
 */
class FixedListRegistry
{
    public function __construct(
        private readonly AdequacyDecisionCountryList $adequacyDecisionCountryList,
        private readonly TransferMechanismList $transferMechanismList,
    ) {
    }

    /**
     * @return list<FixedListColumn<covariant Model>>
     */
    public function columns(): array
    {
        return [
            new FixedListColumn(AvgResponsibleProcessingRecord::class, 'country', $this->adequacyDecisionCountryList),
            new FixedListColumn(AvgProcessorProcessingRecord::class, 'country', $this->adequacyDecisionCountryList),
            new FixedListColumn(DpiaPrescanRecord::class, 'transfer_mechanism', $this->transferMechanismList),
        ];
    }
}
