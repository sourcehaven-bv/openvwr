<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource\Pages;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Pages\CreateRecord;
use App\Filament\Resources\OrganisationUserResource;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function __;
use function in_array;
use function sprintf;

class CreateOrganisationUser extends CreateRecord
{
    protected static string $resource = OrganisationUserResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('user.organisation_role_attach');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('user.model_singular'))
                ->schema([
                    TextInput::make('email')
                        ->label(__('user.email'))
                        ->email()
                        ->required()
                        ->rules([
                            static function (): Closure {
                                return static function (string $attribute, string $value, Closure $fail): void {
                                    $organisation = Authentication::organisation();

                                    $userExistsInCurrentOrganisation = User::query()
                                        ->where('email', $value)
                                        ->withOrganisation($organisation)
                                        ->exists();
                                    if ($userExistsInCurrentOrganisation) {
                                        $fail(__('user.organisation_role_attach_exists'));
                                    }

                                    $allowedEmailDomains = $organisation->allowed_email_domains;
                                    if ($allowedEmailDomains === []) {
                                        return;
                                    }

                                    $userEmailDomain = Str::of($value)->after('@')->toString();
                                    if (in_array($userEmailDomain, $allowedEmailDomains, true)) {
                                        return;
                                    }

                                    $allowedEmailDomainsText = Arr::join($allowedEmailDomains, ', ');
                                    $fail(__('user.email_domain_not_allowed', ['allowedEmailDomains' => $allowedEmailDomainsText]));
                                };
                            },
                        ])
                        ->mutateStateForValidationUsing(static function (string $state): string {
                            return Str::lower($state);
                        })
                        ->dehydrateStateUsing(static function (string $state): string {
                            return Str::lower($state);
                        })
                        ->maxLength(255),
                ]),
            Section::make(__('user.organisation_roles'))
                ->schema(self::getOrganisationRoleToggles()),
        ]);
    }

    /**
     * @return array<Section>
     */
    private static function getOrganisationRoleToggles(): array
    {
        $organisationRoleToggleSections = [];
        $includeCpoRoles = Authorization::hasPermission(Permission::USER_ROLE_ORGANISATION_CPO_MANAGE);

        foreach (Role::organisationRoleGroups($includeCpoRoles) as $organisationRoleGroup) {
            $organisationRoleToggles = [];

            foreach ($organisationRoleGroup as $organisationRole) {
                $organisationRoleToggles[] = Toggle::make($organisationRole->value)
                    ->label(__(sprintf('role.%s', $organisationRole->value)));
            }

            $organisationRoleToggleSections[] = Section::make($organisationRoleToggles)->columns();
        }

        return $organisationRoleToggleSections;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        Assert::keyExists($data, 'email');
        Assert::string($data['email']);
        $organisation = Authentication::organisation();

        /** @var User $user */
        $user = User::firstOrCreate([
            'email' => $data['email'],
        ], [
            'name' => $data['email'],
        ]);
        $user->organisations()->attach($organisation);
        $user->save();
        $organisationRoles = OrganisationUserResource::getOrganisationUserRoleOptions();

        foreach ($organisationRoles as $organisationRole) {
            Assert::keyExists($data, $organisationRole->value);
            Assert::boolean($data[$organisationRole->value]);

            if ($data[$organisationRole->value] !== true) {
                continue;
            }

            $organisationUserRole = new OrganisationUserRole();
            $organisationUserRole->role = $organisationRole;
            $organisationUserRole->organisation_id = $organisation->id;
            $user->organisationRoles()->save($organisationUserRole);
        }

        return $user;
    }
}
