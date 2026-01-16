@php
    /** @var App\Models\SnapshotApproval $snapshotApproval */
@endphp
<x-mail::message>
<div>
    <p>
        {{ __('snapshot_approval.mail_single_sign_request_text') }}<br>
        <x-mail::button :url="$link">{{ __('snapshot_approval.mail_single_sign_request_button_text') }}</x-mail::button>
    </p>
    <p>
        {{ __('snapshot.snapshot_source_type') }}: {{ __(sprintf('%s.model_singular', Str::snake(class_basename($snapshotApproval->snapshot->snapshot_source_type)))) }}<br>
        {{ __('snapshot.name') }}: {{ Str::mailSafe($snapshotApproval->snapshot->name) }}<br>
        {{ __('snapshot.version') }}: {{ Str::mailSafe($snapshotApproval->snapshot->version) }}<br>
    </p>
</div>
</x-mail::message>
