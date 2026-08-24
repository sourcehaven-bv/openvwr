<?php

declare(strict_types=1);

namespace App\FixedLists;

use App\FixedLists\Lists\AdequacyDecisionCountryList;
use App\FixedLists\Lists\TransferMechanismList;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Webmozart\Assert\Assert;

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
            new FixedListColumn(
                AvgResponsibleProcessingRecord::class,
                'country',
                $this->adequacyDecisionCountryList,
                $this->countryOtherValues(),
            ),
            new FixedListColumn(
                AvgProcessorProcessingRecord::class,
                'country',
                $this->adequacyDecisionCountryList,
                $this->countryOtherValues(),
            ),
            new FixedListColumn(DpiaPrescanRecord::class, 'transfer_mechanism', $this->transferMechanismList),
        ];
    }

    /**
     * The "anders, namelijk" sentinel is a valid country value: it routes the answer to the country_other
     * text field. It is not a country, so it does not belong in the list, but it is not a data error either.
     *
     * Every locale is included because the stored value is the translated label, so records created under a
     * different locale hold a different spelling of the same sentinel.
     *
     * @return list<string>
     */
    private function countryOtherValues(): array
    {
        $values = [];
        foreach (['nl', 'en'] as $locale) {
            $value = Lang::get('general.country_other', [], $locale);
            Assert::string($value);

            $values[] = $value;
        }

        return $values;
    }
}
