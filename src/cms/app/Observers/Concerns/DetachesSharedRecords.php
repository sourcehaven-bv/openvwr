<?php

declare(strict_types=1);

namespace App\Observers\Concerns;

use App\Components\Uuid\UuidInterface;
use App\Facades\AuditLog;
use App\Models\Contracts\TenantAware;
use App\Models\User;
use App\Services\AuditLog\Register\LinksDetachedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;
use Webmozart\Assert\Assert;

use function array_values;

trait DetachesSharedRecords
{
    /**
     * Remove only the pivot rows: the linked records are shared registrations of
     * the organisation that outlive any single processing. Each removal is audit logged.
     *
     * @param BelongsToMany<covariant Model, covariant Model, covariant Pivot, covariant string> $relation
     * @param list<string>|null $relatedIds the links to remove; null removes all
     */
    private function detachShared(Model&TenantAware $record, BelongsToMany $relation, ?array $relatedIds = null): void
    {
        $relatedIds ??= array_values($relation->allRelatedIds()->all());
        Assert::allString($relatedIds);

        if ($relatedIds === []) {
            return;
        }

        $relation->detach($relatedIds);

        $user = Auth::user();
        $recordId = $record->getKey();
        Assert::isInstanceOf($recordId, UuidInterface::class);

        AuditLog::register(new LinksDetachedEvent(
            $user instanceof User ? $user : null,
            $record->getOrganisation()->id->toString(),
            $record->getMorphClass(),
            $recordId->toString(),
            $relation->getRelationName(),
            $relatedIds,
        ));
    }
}
