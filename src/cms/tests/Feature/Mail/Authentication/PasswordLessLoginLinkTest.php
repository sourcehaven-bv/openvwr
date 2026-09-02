<?php

declare(strict_types=1);

use App\Facades\DateFormat;
use App\Mail\Authentication\PasswordLessLoginLink;
use App\Models\UserLoginToken;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Tests\Helpers\ConfigTestHelper;

it('contains the correct token', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();

    $mail = new PasswordLessLoginLink($userLoginToken);
    $request = Request::create($mail->link);

    expect($request->get('token'))
        ->toBe($userLoginToken->token);
});

it('contains the correct expiry time', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();
    $tokenExpiryInMinutes = fake()->numberBetween(1, 9);

    ConfigTestHelper::set('auth.passwordless.token_expiry_minutes', $tokenExpiryInMinutes);

    $mail = new PasswordLessLoginLink($userLoginToken);
    $request = Request::create($mail->link);

    expect((int) $request->get('expires'))
        ->toBe(CarbonImmutable::now()->timestamp + $tokenExpiryInMinutes * 60);
});

it('has a subject without a bracketed application name', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();
    $appName = ConfigTestHelper::get('app.name');

    $mailable = new PasswordLessLoginLink($userLoginToken);

    // The sender already identifies us, so the subject carries no prefix.
    $mailable->assertHasSubject(__('auth.passwordless_login_subject'));
    expect(__('auth.passwordless_login_subject'))
        ->not()->toContain(sprintf('[%s]', $appName));
});

it('addresses the recipient and explains what to do', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();
    $appName = ConfigTestHelper::get('app.name');

    $mailable = new PasswordLessLoginLink($userLoginToken);

    // The template strips punctuation from the name, so assert on the same
    // form the mail actually renders.
    $mailable->assertSeeInHtml(__('auth.passwordless_login_greeting', [
        'userName' => Str::mailSafe($userLoginToken->user->name),
    ]), false);
    $mailable->assertSeeInHtml(__('auth.passwordless_login_text', ['appName' => $appName]), false);
    $mailable->assertSeeInHtml(__('auth.passwordless_login_button_text', ['appName' => $appName]), false);
});

it('states when the link expires and that it is single use', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();

    $mailable = new PasswordLessLoginLink($userLoginToken);

    $mailable->assertSeeInHtml(__('auth.passwordless_login_expiry', [
        'validUntil' => DateFormat::forValidUntilShort($userLoginToken->expires_at),
    ]), false);
});

it('reassures a recipient who did not request the mail', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();

    (new PasswordLessLoginLink($userLoginToken))
        ->assertSeeInHtml(__('auth.passwordless_login_ignore'), false);
});

it('spells out the link for clients that strip the button', function (): void {
    $userLoginToken = UserLoginToken::factory()->create();

    $mailable = new PasswordLessLoginLink($userLoginToken);

    $mailable->assertSeeInHtml(__('auth.passwordless_login_fallback'), false);
    // The href is HTML-escaped in the rendered mail, so compare like for like.
    $mailable->assertSeeInHtml(htmlspecialchars($mailable->link, ENT_QUOTES), false);
});
