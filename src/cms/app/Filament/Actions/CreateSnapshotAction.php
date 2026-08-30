<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\Authorization\Permission;
use App\Enums\Notification\NotificationStream;
use App\Facades\Authorization;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Mail\SnapshotApproval\ApprovalRequest;
use App\Models\Contracts\SnapshotSource;
use App\Services\Notification\NotificationRecipientService;
use App\Services\Snapshot\SnapshotFactory;
use App\Services\Snapshot\SnapshotReadinessService;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function json_encode;
use function md5;
use function sprintf;
use function str;

use const JSON_UNESCAPED_UNICODE;

class CreateSnapshotAction extends Action
{
    public static function make(?string $name = 'snapshot_create'): static
    {
        return parent::make($name)
            ->label(__('snapshot.create'))
            ->visible(Authorization::hasPermission(Permission::SNAPSHOT_CREATE))
            ->requiresConfirmation()
            ->action(static function (Model $record, SnapshotFactory $snapshotFactory): void {
                self::createSnapshotAndNotify($record, $snapshotFactory);
            })
            ->after(static function (Component $livewire): void {
                $livewire->dispatch(SnapshotsRelationManager::REFRESH_TABLE_EVENT);
            });
    }

    /**
     * @param array<string, mixed> $snapshotData
     */
    public static function makeWithChangesCheck(?array $snapshotData, string $savedDataHash, string $name = 'snapshot_create'): static
    {
        return self::make($name)
            // Surfaced up front, so an incomplete record is apparent before the user
            // commits to creating a version rather than only after trying.
            ->modalDescription(static function (Component $livewire): ?string {
                return self::describeReadiness($livewire);
            })
            ->action(static function (
                CreateSnapshotAction $action,
                Component $livewire,
                Model $record,
                SnapshotFactory $snapshotFactory,
            ) use (
                $snapshotData,
                $savedDataHash,
            ): void {
                // Reports the missing fields by name and halts; this replaces the plain
                // `validate()` call that used to surface them as inline form errors.
                self::haltOnIncompleteRecord($action, $livewire);

                $dataHash = self::createDataHash($snapshotData);
                if ($dataHash !== $savedDataHash) {
                    Notification::make()
                        ->title(__('snapshot.unsaved_changes'))
                        ->danger()
                        ->send();

                    // @phpstan-ignore argument.type
                    $livewire->dispatch('close-modal', id: sprintf('%s-action', $livewire->getId()));
                    $action->halt();
                }

                self::createSnapshotAndNotify($record, $snapshotFactory);
            });
    }

    /**
     * Warns in the confirmation modal when required fields are still empty, naming them
     * so the user knows what to complete before the version can be created.
     */
    private static function describeReadiness(Component $livewire): ?string
    {
        $form = self::resolveForm($livewire);
        if (!$form instanceof Form) {
            return null;
        }

        /** @var SnapshotReadinessService $snapshotReadinessService */
        $snapshotReadinessService = App::get(SnapshotReadinessService::class);

        $missingRequiredFields = $snapshotReadinessService->getMissingRequiredFields($form);
        if ($missingRequiredFields === []) {
            return null;
        }

        return sprintf(
            '%s %s',
            __('snapshot.not_ready_help'),
            $snapshotReadinessService->buildMissingRequiredFieldsMessage($missingRequiredFields),
        );
    }

    private static function resolveForm(Component $livewire): ?Form
    {
        if (!$livewire instanceof HasForms) {
            return null;
        }

        return $livewire->getForm('form');
    }

    /**
     * Reports concretely which required fields are still empty, and on which step, so the
     * user does not have to hunt through a long wizard for a generic "required" message.
     *
     * Halts the action when the record is not complete enough for a version.
     */
    private static function haltOnIncompleteRecord(CreateSnapshotAction $action, Component $livewire): void
    {
        $form = self::resolveForm($livewire);
        if (!$form instanceof Form) {
            return;
        }

        /** @var SnapshotReadinessService $snapshotReadinessService */
        $snapshotReadinessService = App::get(SnapshotReadinessService::class);

        $missingRequiredFields = $snapshotReadinessService->getMissingRequiredFields($form);
        if ($missingRequiredFields === []) {
            return;
        }

        Notification::make()
            ->title(__('snapshot.incomplete'))
            ->body($snapshotReadinessService->buildMissingRequiredFieldsMessage($missingRequiredFields))
            ->persistent()
            ->danger()
            ->send();

        // @phpstan-ignore argument.type
        $livewire->dispatch('close-modal', id: sprintf('%s-action', $livewire->getId()));
        $action->halt();
    }

    private static function createSnapshotAndNotify(Model $record, SnapshotFactory $snapshotFactory): void
    {
        Assert::isInstanceOf($record, SnapshotSource::class);
        $snapshot = $snapshotFactory->fromSnapshotSource($record);

        /** @var NotificationRecipientService $notificationRecipientService */
        $notificationRecipientService = App::get(NotificationRecipientService::class);

        $users = $notificationRecipientService->getRecipients(
            NotificationStream::SNAPSHOT_CREATED,
            $record->getOrganisation(),
        );

        if ($users->isNotEmpty()) {
            Mail::to($users)
                ->queue(new ApprovalRequest($snapshot));
        }

        Notification::make()
            ->title(__('snapshot.created'))
            ->success()
            ->send();
    }

    /**
     * @param array<string, mixed> $data
     *
     * To compare the hash, we need to rebuild hash from the current state (data). Filament does not provide a method for creating a hash,
     * so we have to copy the functionality here. See \Filament\Pages\Concerns\HasUnsavedDataChangesAlert
     */
    private static function createDataHash(?array $data): string
    {
        $jsonEncodedString = json_encode($data, JSON_UNESCAPED_UNICODE);
        Assert::string($jsonEncodedString);

        /** @SuppressWarnings("php:S4790") hash algorithm is not used in a sensitive context here */
        return md5((string) str($jsonEncodedString)->replace('\\', ''));
    }
}
