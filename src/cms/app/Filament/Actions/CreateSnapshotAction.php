<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Facades\Authorization;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Mail\SnapshotApproval\ApprovalRequest;
use App\Models\Contracts\SnapshotSource;
use App\Services\Snapshot\SnapshotFactory;
use App\Services\User\UserByRoleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function array_key_exists;
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
            ->form([
                Checkbox::make('notify_po')
                    ->label(__('snapshot_approval.notify_po'))
                    ->default(true),
            ])
            ->requiresConfirmation()
            ->action(static function (?array $data, Model $record, SnapshotFactory $snapshotFactory): void {
                Assert::isMap($data);
                self::createSnapshotAndNotify($data, $record, $snapshotFactory);
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
            ->action(static function (
                ?array $data,
                CreateSnapshotAction $action,
                Component $livewire,
                Model $record,
                SnapshotFactory $snapshotFactory,
            ) use (
                $snapshotData,
                $savedDataHash,
            ): void {
                try {
                    $livewire->validate();
                } catch (ValidationException $validationException) {
                    // @phpstan-ignore argument.type
                    $livewire->dispatch('close-modal', id: sprintf('%s-action', $livewire->getId()));

                    throw $validationException;
                }

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

                Assert::isMap($data);
                self::createSnapshotAndNotify($data, $record, $snapshotFactory);
            });
    }

    /**
     * @param ?array<string, mixed> $data
     */
    private static function createSnapshotAndNotify(?array $data, Model $record, SnapshotFactory $snapshotFactory): void
    {
        Assert::isInstanceOf($record, SnapshotSource::class);
        $snapshot = $snapshotFactory->fromSnapshotSource($record);

        if ($data !== null && array_key_exists('notify_po', $data) && $data['notify_po'] === true) {
            /** @var UserByRoleService $userByRoleService */
            $userByRoleService = App::get(UserByRoleService::class);

            $users = $userByRoleService->getUsersByOrganisationRole(
                $record->getOrganisation(),
                [Role::PRIVACY_OFFICER],
            );

            if ($users->isNotEmpty()) {
                Mail::to($users)
                    ->queue(new ApprovalRequest($snapshot));
            }
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
