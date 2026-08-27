<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\OrganisationCollection;
use App\Collections\OrganisationUserRoleCollection;
use App\Collections\SnapshotApprovalCollection;
use App\Collections\UserCollection;
use App\Collections\UserGlobalRoleCollection;
use App\Collections\UserLoginTokenCollection;
use App\Enums\Notification\NotificationStream;
use App\Enums\RegisterLayout;
use App\Enums\Snapshot\MandateholderNotifyBatch;
use App\Enums\Snapshot\MandateholderNotifyDirectly;
use App\Models\Builders\UserBuilder;
use App\Models\Concerns\HasRoles;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use MinVWS\AuditLogger\Contracts\LoggableUser;
use Webmozart\Assert\Assert;

use function sprintf;

/**
 * @property string $email
 * @property string $logName
 * @property MandateholderNotifyBatch $mandateholder_notify_batch
 * @property MandateholderNotifyDirectly $mandateholder_notify_directly
 * @property string $name
 * @property Collection<int, NotificationStream> $notification_exclusions
 * @property mixed|null $otp_secret
 * @property CarbonImmutable|null $otp_confirmed_at
 * @property mixed $password
 * @property RegisterLayout $register_layout
 * @property string|null $remember_token
 *
 * @property-read UserGlobalRoleCollection $globalRoles
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read OrganisationUserRoleCollection $organisationRoles
 * @property-read OrganisationCollection $organisations
 * @property-read SnapshotApprovalCollection $snapshotApprovals
 * @property-read UserLoginTokenCollection $userLoginTokens
 */
#[UseEloquentBuilder(UserBuilder::class)]
class User extends Authenticatable implements FilamentUser, HasTenants, LoggableUser, MustVerifyEmail
{
    use HasRoles;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuidAsId;
    use Notifiable;

    protected static string $collectionClass = UserCollection::class;
    protected $fillable = [
        'email',
        'mandateholder_notify_batch',
        'mandateholder_notify_directly',
        'name',
        'notification_exclusions',
        'register_layout',
    ];
    protected $hidden = [
        'otp_secret',
        'remember_token',
    ];

    public function casts(): array
    {
        return [
            'mandateholder_notify_batch' => MandateholderNotifyBatch::class,
            'mandateholder_notify_directly' => MandateholderNotifyDirectly::class,
            'notification_exclusions' => AsEnumCollection::of(NotificationStream::class),
            'otp_secret' => 'encrypted',
            'otp_confirmed_at' => 'datetime',
            'register_layout' => RegisterLayout::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function receivesNotification(NotificationStream $notificationStream): bool
    {
        return !$this->notification_exclusions->contains($notificationStream);
    }

    /**
     * @return BelongsToMany<Organisation, $this, OrganisationUser>
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class)
            ->using(OrganisationUser::class)
            ->withTimestamps();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (!$tenant instanceof Organisation) {
            return false;
        }

        return $this->organisations->pluck('id')->contains($tenant->id);
    }

    /**
     * @return OrganisationCollection|array<Organisation>
     */
    public function getTenants(Panel $panel): array|OrganisationCollection
    {
        return $this->organisations;
    }

    /**
     * @return HasMany<UserGlobalRole, $this>
     */
    public function globalRoles(): HasMany
    {
        return $this->hasMany(UserGlobalRole::class);
    }

    /**
     * @return HasMany<OrganisationUserRole, $this>
     */
    public function organisationRoles(): HasMany
    {
        return $this->hasMany(OrganisationUserRole::class);
    }

    /**
     * @return HasMany<SnapshotApproval, $this>
     */
    public function snapshotApprovals(): HasMany
    {
        return $this->hasMany(SnapshotApproval::class, 'assigned_to');
    }

    /**
     * @return HasMany<UserLoginToken, $this>
     */
    public function userLoginTokens(): HasMany
    {
        return $this->hasMany(UserLoginToken::class);
    }

    /**
     * @return Attribute<string, never>
     */
    public function logName(): Attribute
    {
        return Attribute::make(static function (mixed $value, array $attributes): string {
            $name = $attributes['name'];
            Assert::string($name);

            $email = $attributes['email'];
            Assert::string($email);

            return sprintf('%s (%s)', $name, $email);
        });
    }

    public function getAuditId(): string
    {
        return $this->id->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        $roles = $this->globalRoles
            ->map(static function (UserGlobalRole $role) {
                return $role->role->value;
            })
            ->toArray();
        Assert::allString($roles);

        return $roles;
    }
}
