<?php

declare(strict_types=1);

use App\Filament\Resources\ContactPersonResource;
use App\Models\ContactPerson;
use App\Models\Organisation;
use App\Models\User;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\PratiqueTestHelper;

/*
 * End-to-end tenant routing under AUTH_DRIVER=pratique.
 *
 * The other Pratique tests drive the middleware and services directly. These boot
 * the application with the driver switched on, so the panel registers its
 * pratique wiring, and send real HTTP requests through the registered Filament
 * routes, including Filament's own tenant middleware. Only the proxy is faked: each
 * request carries an assertion signed the way Pratique signs it.
 *
 * The panel is built while the application boots, so the driver has to be in the
 * environment before then. Setting config in a test would be too late.
 */

/** Laravel's own repository is immutable; this one writes to the same places. */
function authDriverEnvironment(): RepositoryInterface
{
    return RepositoryBuilder::createWithDefaultAdapters()->addAdapter(PutenvAdapter::class)->make();
}

beforeAll(static function (): void {
    authDriverEnvironment()->set('AUTH_DRIVER', 'pratique');
});

// Cleared rather than restored: the next application boot loads AUTH_DRIVER from
// .env again, or falls back to the builtin default when .env does not set it.
afterAll(static function (): void {
    authDriverEnvironment()->clear('AUTH_DRIVER');
});

beforeEach(function (): void {
    $this->pratique = new PratiqueTestHelper();
    $this->pratique->publishJwks();

    $this->acme = Organisation::factory()->create(['slug' => 'acme']);
    $this->globex = Organisation::factory()->create(['slug' => 'globex']);
});

/**
 * A request as the proxy forwards it: alice, signed in to the given organisation.
 *
 * @param array<string, mixed> $claims
 */
function asAliceIn(string $organisationSlug, string $uri, array $claims = []): TestResponse
{
    $test = test();

    return $test->withHeader('Authorization', 'Bearer ' . $test->pratique->assertion([
        'sub' => 'usr_alice',
        'email' => 'alice@example.org',
        'org_slug' => $organisationSlug,
        'roles' => ['chief-privacy-officer'],
        ...$claims,
    ]))->get($uri);
}

it('boots the panel with the pratique driver', function (): void {
    expect(config('auth.driver'))->toBe('pratique');
});

it('sends the panel root to the tenant the assertion names', function (): void {
    asAliceIn('acme', '/')->assertRedirect('/acme');
});

/*
 * The local membership list grows as a user visits organisations, so it can hold
 * more than the one the proxy has selected. The landing redirect must follow the
 * assertion, not whichever membership happens to come first.
 */
it('lands in the asserted tenant when the user belongs to several', function (): void {
    asAliceIn('acme', '/acme')->assertSuccessful();

    asAliceIn('globex', '/')->assertRedirect('/globex');
});

it('reaches a tenant page through the registered panel routes', function (): void {
    asAliceIn('acme', '/acme')->assertSuccessful();
});

/*
 * Switching organisation happens in the proxy, which then signs assertions for the
 * new one. The app must follow: the new tenant opens, and the old one is closed
 * because the current assertion no longer grants it.
 */
it('follows a tenant switch made in the proxy', function (): void {
    asAliceIn('acme', '/acme')->assertSuccessful();

    asAliceIn('globex', '/globex')->assertSuccessful();
    asAliceIn('globex', '/acme')->assertForbidden();
});

/*
 * Every tenant URL but the asserted one is refused, so the tenant menu must not
 * offer them. Switching goes through the proxy's picker instead.
 */
it('offers a switch through the proxy instead of other tenants', function (): void {
    asAliceIn('acme', '/acme')->assertSuccessful();
    User::query()->where('email', 'alice@example.org')->sole()->organisations()->attach($this->globex);

    asAliceIn('acme', '/acme')
        ->assertSuccessful()
        ->assertSee('/__pratique/auth/select-tenant?rd=%2F', false)
        ->assertSee(__('user.switch_organisation'))
        ->assertDontSee(url('/globex'));
});

it('links the profile to the proxy account page instead of OTP enrolment', function (): void {
    asAliceIn('acme', '/acme/profile')
        ->assertSuccessful()
        ->assertSee('/__pratique/admin/account?rd=%2Facme%2Fprofile', false)
        ->assertSee(__('user.profile.pratique_account.heading'))
        ->assertDontSee(__('user.profile.one_time_password.title'));
});

it('appends the return page to an account URL that has a query', function (): void {
    config(['auth.pratique.account_url' => 'https://id.example.org/admin/account?lang=nl']);

    asAliceIn('acme', '/acme/profile')
        ->assertSuccessful()
        ->assertSee('https://id.example.org/admin/account?lang=nl&amp;rd=%2Facme%2Fprofile', false);
});

it('refuses another organisation\'s tenant URL', function (): void {
    asAliceIn('acme', '/globex')->assertForbidden();
});

it('refuses an organisation the app does not know', function (): void {
    asAliceIn('initech', '/initech')->assertForbidden();
});

it('refuses a tenant page without an assertion', function (): void {
    $this->get('/acme')->assertForbidden();
});

it('refuses the panel root without an assertion', function (): void {
    $this->get('/')->assertForbidden();
});

/*
 * The identity is set for one request only. If it were written to the session, a
 * later request without an assertion would ride on it past the proxy.
 */
it('does not carry the identity over to a request without an assertion', function (): void {
    asAliceIn('acme', '/acme')->assertSuccessful();

    $this->withHeaders(['Authorization' => ''])->get('/acme')->assertForbidden();
});

it('shows only the tenant\'s own records', function (): void {
    $own = ContactPerson::factory()->recycle($this->acme)->create(['name' => 'Own Contact']);
    $foreign = ContactPerson::factory()->recycle($this->globex)->create(['name' => 'Foreign Contact']);

    asAliceIn('acme', ContactPersonResource::getUrl(tenant: $this->acme))
        ->assertSuccessful()
        ->assertSee($own->name)
        ->assertDontSee($foreign->name);
});

it('does not open another tenant\'s record inside the asserted tenant', function (): void {
    $foreign = ContactPerson::factory()->recycle($this->globex)->create();

    asAliceIn('acme', ContactPersonResource::getUrl('edit', ['record' => $foreign], tenant: $this->acme))
        ->assertNotFound();
});

/*
 * The proxy owns the session. Ending only the app's side would send the user
 * straight back in on the next request, so logout must hand over to the proxy.
 */
it('hands logout over to the proxy', function (): void {
    $test = test();

    $test->withHeader('Authorization', 'Bearer ' . $test->pratique->assertion([
        'sub' => 'usr_alice',
        'org_slug' => 'acme',
    ]))->post('/logout')->assertRedirect('/__pratique/auth/logout');
});
