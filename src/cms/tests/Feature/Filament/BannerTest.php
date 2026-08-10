<?php

declare(strict_types=1);

use App\Enums\BannerLevel;
use App\Enums\RouteName;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use Tests\Helpers\Model\UserTestHelper;

/*
 * The banner is operator-configured through `config/banner.php` and rendered on
 * every panel page via a BODY_START render hook. Rendering the view directly is
 * what exercises the empty/level/escaping branches.
 */

function renderBanner(?string $message, ?string $level = null): string
{
    config(['banner.message' => $message]);
    config(['banner.level' => $level ?? 'warning']);

    return view('filament.banner')->render();
}

it('renders nothing when no message is configured', function (): void {
    expect(trim(renderBanner(null)))->toBe('');
});

it('renders nothing when the message is blank', function (): void {
    expect(trim(renderBanner('   ')))->toBe('');
});

it('renders the configured message', function (): void {
    $html = renderBanner('This is a demo env. Do not put production content here.');

    expect($html)
        ->toContain('This is a demo env. Do not put production content here.')
        ->toContain('role="status"');
});

it('applies the modifier class for the configured level', function (string $level): void {
    $html = renderBanner(fake()->sentence(), $level);

    expect($html)->toContain(BannerLevel::from($level)->cssClass());
})->with(['info', 'warning', 'danger']);

/*
 * The colours live in the panel theme, not in utility classes on the element:
 * Tailwind scans app/Filament and the Blade views, so utilities returned from an
 * enum in app/Enums are never compiled and the banner renders as a transparent
 * bar. This pins every modifier to a rule that actually exists in the stylesheet.
 */
it('has a theme rule for every level', function (BannerLevel $level): void {
    $theme = file_get_contents(resource_path('css/filament/admin/theme.css'));

    expect($theme)->toContain('.' . $level->cssClass() . ' {');
})->with(BannerLevel::cases());

it('falls back to warning when the level is not recognised', function (): void {
    $html = renderBanner(fake()->sentence(), 'nonsense');

    expect($html)->toContain(BannerLevel::WARNING->cssClass());
});

/*
 * The message is plain text by contract. Escaping keeps a stray angle bracket in
 * an operator's wording from breaking the surrounding markup.
 */
it('escapes html in the message', function (): void {
    $html = renderBanner('<script>alert(1)</script>');

    expect($html)
        ->not->toContain('<script>alert(1)</script>')
        ->toContain('&lt;script&gt;');
});

/*
 * The render hook is what puts the banner on real pages. The login page matters
 * as much as the panel itself: a "this is a demo environment" notice is most
 * useful before anyone signs in and starts entering data.
 */

it('shows the banner on a panel page', function (): void {
    $message = 'This is a demo env. Do not put production content here.';
    config(['banner.message' => $message]);

    $this->asFilamentUser(UserTestHelper::create())
        ->get(AvgResponsibleProcessingRecordResource::getUrl('create'))
        ->assertOk()
        ->assertSee($message);
});

it('shows the banner on the login page', function (): void {
    $message = 'This is a demo env. Do not put production content here.';
    config(['banner.message' => $message]);

    $this->get(route(RouteName::FILAMENT_ADMIN_AUTH_LOGIN))
        ->assertOk()
        ->assertSee($message);
});

it('shows no banner on the login page when unconfigured', function (): void {
    config(['banner.message' => null]);

    $this->get(route(RouteName::FILAMENT_ADMIN_AUTH_LOGIN))
        ->assertOk()
        ->assertDontSee('fi-banner', escape: false);
});
