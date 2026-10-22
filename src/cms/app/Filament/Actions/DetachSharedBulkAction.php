<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Components\Uuid\UuidInterface;
use App\Models\Contracts\TenantAware;
use App\Observers\Concerns\DetachesSharedRecords;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webmozart\Assert\Assert;

use function array_values;

/**
 * Removes only the links from the owner record to the selected shared records,
 * such as documents or responsibles, and audit logs the removal.
 */
class DetachSharedBulkAction extends DetachBulkAction
{
    use DetachesSharedRecords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            $this->process(function (Collection $records, Table $table): void {
                $relationship = $table->getRelationship();
                Assert::isInstanceOf($relationship, BelongsToMany::class);

                $owner = $relationship->getParent();
                Assert::isInstanceOf($owner, TenantAware::class);

                $relatedIds = $records->map(static function (Model $record): string {
                    $relatedId = $record->getKey();
                    Assert::isInstanceOf($relatedId, UuidInterface::class);

                    return $relatedId->toString();
                });

                $this->detachShared($owner, $relationship, array_values($relatedIds->all()));
            });

            $this->success();
        });
    }
}
