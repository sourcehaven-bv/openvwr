<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Components\Uuid\UuidInterface;
use App\Models\Casts\UuidCast;
use App\Models\Organisation;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property UuidInterface $organisation_id
 *
 * @property-read Organisation $organisation
 */
trait HasOrganisation
{
    /**
     * Start a query restricted to the active organisation.
     *
     * Keeping query creation and tenant scoping in one operation makes the
     * safe path the easy path and allows static analysis to reject unscoped
     * query entry points in request-facing code.
     *
     * @return Builder<static>
     */
    // Snapshot overrides this only to preserve its custom builder's static type.
    // phpcs:ignore Universal.FunctionDeclarations.RequireFinalMethodsInTraits.NonFinalMethodFound
    public static function tenantQuery(): Builder
    {
        return static::query()->withGlobalScope('tenant', new TenantScope());
    }

    final public function initializeHasOrganisation(): void
    {
        $this->mergeCasts([
            'organisation_id' => UuidCast::class,
            // Copyable entities carry this cross-org copy watermark; harmless no-op on
            // organisation-scoped tables that do not have the column (lookups, snapshots).
            'last_synced_at' => 'datetime',
        ]);
        $this->mergeFillable(['organisation_id']);
    }

    /**
     * @return BelongsTo<Organisation, $this>
     */
    final public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    final public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }
}
