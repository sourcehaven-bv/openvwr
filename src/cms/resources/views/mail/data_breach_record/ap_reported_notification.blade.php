@php
    /** @var App\Models\DataBreachRecord $dataBreachRecord */
@endphp
<x-mail::message>
<div>
    <p>
        {{ __('data_breach_record.mail_notification_text') }}<br>
        <x-mail::button :url="$link">{{ __('data_breach_record.mail_notification_button_text') }}</x-mail::button>
    </p>
    <p>
        {{ __('data_breach_record.number') }}: {{ Str::mailSafe($dataBreachRecord->entityNumber->number) }}<br>
        {{ __('data_breach_record.name') }}: {{ Str::mailSafe($dataBreachRecord->name) }}<br>
    </p>
</div>
</x-mail::message>
