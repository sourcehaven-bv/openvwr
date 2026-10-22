<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Components\Uuid\UuidInterface;
use App\Enums\Queue;
use App\Events\StaticWebsite\BuildEvent;
use App\Facades\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLog\Transfer\TransferFailureReason;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use App\Services\BuildContextService;
use App\Transfer\Import\BundleImporter;
use App\Transfer\TransferBundleStorage;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

use function __;

class TransferImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const string DISK = TransferBundleStorage::DISK;

    /**
     * @param array<string, array{selected: bool, strategy: ?string}> $plan
     */
    public function __construct(
        public readonly string $bundlePath,
        public readonly array $plan,
        public readonly UuidInterface $organisationId,
        public readonly UuidInterface $userId,
    ) {
        $this->onQueue(Queue::DEFAULT);
    }

    public function handle(
        BundleImporter $bundleImporter,
        BuildContextService $buildContextService,
        TransferBundleStorage $bundleStorage,
    ): void {
        $organisation = Organisation::query()->findOrFail($this->organisationId->toString());
        $user = User::query()->findOrFail($this->userId->toString());

        $buildContextService->disableBuild();

        try {
            // The bundle may live in object storage, so work on a local copy:
            // importZip() opens it with ZipArchive, which needs a real path.
            $result = $bundleStorage->withLocalCopy(
                $this->bundlePath,
                fn (string $localPath) => $bundleImporter->importZip($localPath, $this->plan, $organisation, $user),
            );
        } catch (Throwable $exception) {
            Log::error('transfer import failed', ['message' => $exception->getMessage()]);

            AuditLog::register(new TransferImportEvent(
                $user,
                TransferImportEvent::METHOD_BUNDLE,
                $organisation->id->toString(),
                null,
                null,
                TransferFailureReason::from($exception),
            ));

            Notification::make()
                ->title(__('transfer.import_failed'))
                ->icon('heroicon-o-archive-box')
                ->danger()
                ->sendToDatabase($user);

            return;
        } finally {
            $buildContextService->enableBuild();
            $bundleStorage->delete($this->bundlePath);
        }

        AuditLog::register(new TransferImportEvent(
            $user,
            TransferImportEvent::METHOD_BUNDLE,
            $organisation->id->toString(),
            $result->claimedSourceOrganisationId,
            $result,
        ));

        BuildEvent::dispatch();

        Notification::make()
            ->title(__('transfer.import_finished'))
            ->body(__('transfer.import_finished_body', [
                'created' => $result->created,
                'overwritten' => $result->overwritten,
                'skipped' => $result->skipped,
            ]))
            ->icon('heroicon-o-archive-box')
            ->success()
            ->sendToDatabase($user);
    }
}
