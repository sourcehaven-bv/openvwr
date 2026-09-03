<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\LabelColor;
use App\Models\Tag;
use Filament\Facades\Filament;
use Webmozart\Assert\Assert;

/**
 * Picks the colour for a new label.
 *
 * Scoped per organisation: labels are only ever seen next to the other labels
 * of the same organisation, so that is the set the spread has to look good in.
 */
class LabelColorAssigner
{
    public function assign(Tag $tag): LabelColor
    {
        return LabelColor::leastUsed($this->usage($tag));
    }

    /**
     * Count the colours already taken within the organisation.
     *
     * Global scopes are dropped so the count covers the whole organisation
     * rather than the acting user's tenant, and soft-deleted labels keep their
     * claim on a colour because restoring one must not produce a duplicate.
     *
     * @return array<string, int>
     */
    private function usage(Tag $tag): array
    {
        // v5 associates the tenant from its own `creating` observer, which the
        // panel registers when it boots and therefore after this one. So the
        // label may not carry its organisation yet and the current tenant is
        // the same answer: a label is always created within the tenant that is
        // being worked in.
        $organisationId = $tag->organisation_id?->toString()
            ?? Filament::getTenant()?->getKey();

        if ($organisationId === null) {
            return [];
        }

        $rows = Tag::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->where('organisation_id', $organisationId)
            ->whereNotNull('color')
            ->groupBy('color')
            ->select('color')
            ->selectRaw('count(*) as total')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            // The model casts this column, so the attribute comes back as the
            // enum rather than the stored string.
            $color = $row->getAttribute('color');
            Assert::isInstanceOf($color, LabelColor::class);

            $total = $row->getAttribute('total');
            Assert::integerish($total);

            $counts[$color->value] = (int) $total;
        }

        return $counts;
    }
}
