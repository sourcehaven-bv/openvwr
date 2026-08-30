<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Pages\TransferCopy;
use App\Services\CrossOrgAuthorization;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function http_build_query;
use function implode;

class TransferCopyBulkAction extends BulkAction
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'transfer_copy')
            ->label(__('transfer.copy_action'))
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->visible(static fn (): bool => self::canCopyToOtherOrganisation())
            ->deselectRecordsAfterCompletion()
            ->action(static function (Collection $records, TransferCopyBulkAction $action): void {
                $first = $records->first();
                Assert::isInstanceOf($first, Model::class);

                $type = TransferEntityType::fromModel($first);

                $action->redirect(TransferCopy::getUrl(
                    panel: 'admin',
                    tenant: Authentication::organisation(),
                ) . '?' . http_build_query([
                    'type' => $type->value,
                    'records' => implode(',', TransferExportBulkAction::recordIds($records)),
                ]));
            });
    }

    /**
     * Visible only when the user can export here and has at least one other organisation
     * they may copy into — no point offering a copy with nowhere to send it.
     */
    private static function canCopyToOtherOrganisation(): bool
    {
        if (!Authorization::hasPermission(Permission::TRANSFER_EXPORT)) {
            return false;
        }

        $targets = app(CrossOrgAuthorization::class)
            ->copyTargetsFor(Authentication::user(), Authentication::organisation());

        return $targets !== [];
    }
}
