@use(App\Facades\DateFormat)
<x-mail::message>
    <div>
        {{ __('auth.snapshot_sign_login_text', ['userName' => Str::mailSafe($userLoginToken->user->name)]) }}:<br>
        <x-mail::button :url="$link">{{ __('auth.snapshot_sign_login_button_text') }}</x-mail::button>
        <br>
        {{ __('auth.snapshot_sign_login_footer', ['validUntil' => DateFormat::forValidUntilShort($userLoginToken->expires_at)]) }}<br>
    </div>
</x-mail::message>
