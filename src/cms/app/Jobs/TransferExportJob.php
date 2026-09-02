<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Components\Uuid\UuidInterface;
use App\Enums\Queue;
use App\Enums\RouteName;
use App\Models\Organisation;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

use function __;
use function basename;
use function count;
use function now;

class TransferExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const int DOWNLOAD_VALID_DAYS = 7;

    /**
     * @param list<string> $recordIds
     * @param array<string, list<string>> $selectedRelated
     */
    public function __construct(
        public readonly TransferEntityType $recordType,
        public readonly array $recordIds,
        public readonly array $selectedRelated,
        public readonly UuidInterface $organisationId,
        public readonly UuidInterface $userId,
    ) {
        $this->onQueue(Queue::DEFAULT);
    }

    public function handle(BundleExporter $bundleExporter): void
    {
        $organisation = Organisation::query()->findOrFail($this->organisationId);

        $path = $bundleExporter->export($this->recordType, $this->recordIds, $this->selectedRelated, $organisation);

        $user = User::query()->find($this->userId);
        if ($user === null) {
            return;
        }

        $downloadUrl = URL::temporarySignedRoute(
            RouteName::TRANSFER_EXPORT_DOWNLOAD->value,
            now()->addDays(self::DOWNLOAD_VALID_DAYS),
            [
                'filename' => basename($path),
                'user' => $this->userId->toString(),
            ],
        );

        Notification::make()
            ->title(__('transfer.export_ready'))
            ->body(__('transfer.export_ready_body', ['count' => count($this->recordIds)]))
            ->icon('heroicon-o-archive-box-arrow-down')
            ->success()
            ->actions([
                Action::make('download')
                    ->label(__('transfer.download'))
                    ->url($downloadUrl),
            ])
            ->sendToDatabase($user);
    }
}
