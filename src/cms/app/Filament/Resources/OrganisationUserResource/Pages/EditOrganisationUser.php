<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource\Pages;

use App\Facades\Authentication;
use App\Filament\Resources\OrganisationUserResource;
use App\Models\OrganisationUser;
use App\Models\OrganisationUserRole;
use App\Models\User;
use App\Models\UserRelatable;
use App\Rules\CurrentOrganisation;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webmozart\Assert\Assert;

use function __;
use function array_key_exists;

class EditOrganisationUser extends EditRecord
{
    protected static string $resource = OrganisationUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('detach')
                ->label(__('user.organisation_role_detach'))
                ->color('danger')
                ->requiresConfirmation(static function (Action $action): void {
                    $action->modalDescription(__('user.organisation_role_detach_description'));
                    $action->modalSubmitActionLabel(__('general.yes'));
                    $action->modalCancelActionLabel(__('general.no'));
                })
                ->form([
                    Select::make('alternate_user_id')
                        ->label(__('user.organisation_role_detach_alternate_primary_contact'))
                        ->options(static function (User $user): Collection {
                            return User::whereNot('id', $user->id)
                                ->withOrganisation(Authentication::organisation())
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->rules([CurrentOrganisation::forModel(OrganisationUser::class, 'user_id')]),
                ])
                ->action(static function (array $data, Action $action, User $record): void {
                    UserRelatable::where('user_id', $record->id)
                        ->update(['user_id' => $data['alternate_user_id']]);

                    $organisation = Authentication::organisation();
                    $record->organisations()->detach($organisation);
                    $action->redirect(OrganisationUserResource::getUrl());
                }),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;
        Assert::isInstanceOf($user, User::class);

        $organisation = Authentication::organisation();
        $organisationRoles = OrganisationUserResource::getOrganisationUserRoleOptions();

        DB::transaction(function () use ($user, $organisation, $organisationRoles): void {
            $user->organisationRoles()
                ->where('organisation_id', $organisation->id)
                ->whereIn('role', $organisationRoles)
                ->delete();

            $organisationUserRoles = [];

            foreach ($organisationRoles as $organisationRole) {
                Assert::isArray($this->data);
                if (!array_key_exists($organisationRole->value, $this->data)) {
                    continue;
                }

                Assert::boolean($this->data[$organisationRole->value]);
                if ($this->data[$organisationRole->value] !== true) {
                    continue;
                }

                $organisationUserRoles[] = new OrganisationUserRole([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                    'role' => $organisationRole,
                ]);
            }
            $user->organisationRoles()->saveMany($organisationUserRoles);
        });

        $this->redirect(OrganisationUserResource::getUrl('edit', ['record' => $this->getRecord()]));
    }
}
