@php
    /** @var App\Models\Document $document */
@endphp
<x-mail::message>
<div>
    <p>
        {{ __('document.mail_notification_text') }}<br>
        <x-mail::button :url="$link">{{ __('document.mail_notification_button_text') }}</x-mail::button>
    </p>
    <p>
        {{ __('document.name') }}: {{ Str::mailSafe($document->name) }}<br>
        {{ __('document.expires_at') }}: {{ DateFormat::toDate($document->expires_at) }}<br>
    </p>
</div>
</x-mail::message>
