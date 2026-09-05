<?php

declare(strict_types=1);

namespace App\Providers;

use App\Config\Config;
use App\Models\AdminLogEntry;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\LookupListModel;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\PublicWebsiteTree;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\ResponsibleLegalEntity;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Policies\AdminLogEntryPolicy;
use App\Policies\CoreEntityPolicy;
use App\Policies\DataBreachRecordPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ExportPolicy;
use App\Policies\LookupListPolicy;
use App\Policies\ManagementPolicy;
use App\Policies\OrganisationPolicy;
use App\Policies\ResponsibleLegalEntityPolicy;
use App\Policies\ResponsiblePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Services\Authentication\AuthenticationStrategy;
use App\Services\Authentication\AuthenticationStrategyFactory;
use App\Services\Authentication\Pratique\JwksProvider;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueAssertionVerifier;
use App\Services\Authentication\Pratique\PratiqueContext;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookVerifier;
use App\Services\Authentication\PratiqueAuthenticationStrategy;
use App\Services\AuthorizationService;
use App\Services\CrossOrgAuthorization;
use App\Services\OneTimePassword\OneTimePassword;
use App\Services\OneTimePassword\OneTimePasswordManager;
use App\Services\OneTimePassword\TimedOneTimePassword;
use App\Services\OtpService;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as IlluminateAuthServiceProvider;
use Illuminate\Http\Client\Factory as Http;

class AuthServiceProvider extends IlluminateAuthServiceProvider
{
    /** @var array<class-string, class-string> $policies */
    protected $policies = [
        // core entities
        AlgorithmRecord::class => CoreEntityPolicy::class,
        AvgProcessorProcessingRecord::class => CoreEntityPolicy::class,
        AvgResponsibleProcessingRecord::class => CoreEntityPolicy::class,
        DataBreachRecord::class => DataBreachRecordPolicy::class,
        DpiaPrescanRecord::class => CoreEntityPolicy::class,
        DpiaRecord::class => CoreEntityPolicy::class,
        WpgProcessingRecord::class => CoreEntityPolicy::class,

        // lookup lists
        LookupListModel::class => LookupListPolicy::class,

        // management
        AvgGoal::class => ManagementPolicy::class,
        ContactPerson::class => ManagementPolicy::class,
        Document::class => DocumentPolicy::class,
        Processor::class => ManagementPolicy::class,
        Receiver::class => ManagementPolicy::class,
        Responsible::class => ResponsiblePolicy::class,
        Stakeholder::class => ManagementPolicy::class,
        StakeholderDataItem::class => ManagementPolicy::class,
        System::class => ManagementPolicy::class,
        WpgGoal::class => ManagementPolicy::class,

        // other
        AdminLogEntry::class => AdminLogEntryPolicy::class,
        Organisation::class => OrganisationPolicy::class,
        PublicWebsiteTree::class => OrganisationPolicy::class,
        ResponsibleLegalEntity::class => ResponsibleLegalEntityPolicy::class,
        Tag::class => TagPolicy::class,
        User::class => UserPolicy::class,

        // filament
        Export::class => ExportPolicy::class,
    ];

    public function boot(): void
    {
        $this->app->singleton(OneTimePassword::class, function (): OneTimePassword {
            /** @var OneTimePasswordManager $oneTimePasswordManager */
            $oneTimePasswordManager = $this->app->get(OneTimePasswordManager::class);

            /** @var OneTimePassword $oneTimePassword */
            $oneTimePassword = $oneTimePasswordManager->driver(Config::string('auth.one_time_password.driver'));

            return $oneTimePassword;
        });
    }

    public function register(): void
    {
        parent::register();

        // Resolve the auth driver once, at boot: an unknown driver (or `dev`
        // outside local/testing) must fail startup rather than silently
        // authenticate requests some other way.
        $this->app->singleton(
            AuthenticationStrategy::class,
            fn (): AuthenticationStrategy => AuthenticationStrategyFactory::make(
                Config::string('auth.driver', AuthenticationStrategyFactory::DRIVER_BUILTIN),
                $this->app->environment(),
                fn (): AuthenticationStrategy => $this->app->make(PratiqueAuthenticationStrategy::class),
            ),
        );

        // The verified identity belongs to one request, so this is scoped rather
        // than a singleton. A shared instance would let one request's user and
        // organisation answer another's questions — the same shape of bug that
        // leaked roles across tenants when the builtin strategy memoised on a
        // singleton.
        $this->app->scoped(PratiqueContext::class);

        $this->app->singleton(JwksProvider::class, fn (): JwksProvider => new JwksProvider(
            $this->app->make(Http::class),
            $this->app->make(Cache::class),
            self::requiredPratiqueSetting('jwks_url'),
            Config::integer('auth.pratique.jwks_cache_seconds'),
        ));

        $this->app->singleton(
            PratiqueAssertionVerifier::class,
            fn (): PratiqueAssertionVerifier => new PratiqueAssertionVerifier(
                $this->app->make(JwksProvider::class),
                self::requiredPratiqueSetting('issuer'),
                self::requiredPratiqueSetting('audience'),
                Config::integer('auth.pratique.leeway_seconds'),
            ),
        );

        // The webhook verifier shares the key handling with assertions but not
        // the audience check: a webhook token carries no `aud`, and relaxing the
        // assertion verifier to match would weaken the guard on every
        // authenticated request. See PratiqueWebhookVerifier.
        $this->app->singleton(
            PratiqueWebhookVerifier::class,
            fn (): PratiqueWebhookVerifier => new PratiqueWebhookVerifier(
                $this->app->make(JwksProvider::class),
                self::requiredPratiqueSetting('issuer'),
                Config::integer('auth.pratique.leeway_seconds'),
            ),
        );

        $this->app->when(AuthorizationService::class)
            ->needs('$rolesAndPermissions')
            ->giveConfig('permissions.roles_and_permissions');

        $this->app->when(CrossOrgAuthorization::class)
            ->needs('$rolesAndPermissions')
            ->giveConfig('permissions.roles_and_permissions');

        $this->app->when(OtpService::class)
            ->needs('$appName')
            ->giveConfig('app.name');

        $this->app->when(TimedOneTimePassword::class)
            ->needs('$window')
            ->giveConfig('auth.one_time_password.validation_window');
    }

    /**
     * A Pratique setting that has no safe default.
     *
     * The issuer, audience and JWKS URL define who we trust; guessing any of them
     * would mean verifying assertions against the wrong authority. An empty value
     * must therefore stop the app rather than weaken the check — and because these
     * bindings are only resolved under the pratique driver, this cannot break a
     * builtin deployment that has never heard of them.
     *
     * @throws PratiqueAssertionException
     */
    private static function requiredPratiqueSetting(string $key): string
    {
        $value = Config::stringOrNull('auth.pratique.' . $key);

        if ($value === null || $value === '') {
            throw PratiqueAssertionException::misconfigured('auth.pratique.' . $key);
        }

        return $value;
    }
}
