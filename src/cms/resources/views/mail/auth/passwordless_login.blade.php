@use(App\Facades\DateFormat)
@use(Illuminate\Support\Str)
<x-mail::message>
# {{ __('auth.passwordless_login_greeting', ['userName' => Str::mailSafe($userLoginToken->user->name)]) }}

{{ __('auth.passwordless_login_text', ['appName' => config('app.name')]) }}

<x-mail::button :url="$link">{{ __('auth.passwordless_login_button_text', ['appName' => config('app.name')]) }}</x-mail::button>

{{ __('auth.passwordless_login_expiry', ['validUntil' => DateFormat::forValidUntilShort($userLoginToken->expires_at)]) }}

{{ __('auth.passwordless_login_ignore') }}

{{-- The link is spelled out because a fair number of mail clients strip or
     rewrite the button, and the recipient has no other way in. --}}
<x-slot:subcopy>
{{ __('auth.passwordless_login_fallback') }}
<span class="break-all">[{{ $link }}]({{ $link }})</span>
</x-slot:subcopy>
</x-mail::message>
