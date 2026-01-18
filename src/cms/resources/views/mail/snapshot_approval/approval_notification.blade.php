@php
    /** @var App\Models\SnapshotApproval $snapshotApproval */
@endphp
<x-mail::message>
<div>
    <p>
        {{ __('snapshot_approval.mail_approval_notification_text') }}<br>
        <x-mail::button :url="$link">{{ __('snapshot_approval.mail_approval_notification_button_text') }}</x-mail::button>
    </p>
    <p>
        {{ __('snapshot.snapshot_source_type') }}: {{ __(sprintf('%s.model_singular', Str::snake(class_basename($snapshotApproval->snapshot->snapshot_source_type)))) }}<br>
        {{ __('snapshot.snapshot_source_display_name') }}: {{ Str::mailSafe($snapshotApproval->snapshot->snapshotSource?->getDisplayName()) }}<br>
        <br>
        {{ __('snapshot.name') }}: {{ Str::mailSafe($snapshotApproval->snapshot->name) }}<br>
        {{ __('snapshot.version') }}: {{ Str::mailSafe($snapshotApproval->snapshot->version) }}<br>
        <br>
        {{ __('user.name') }}: {{ Str::mailSafe($snapshotApproval->assignedTo->name) }}<br>
        {{ __('snapshot_approval.status') }}: {{ __(sprintf('snapshot_approval_status.%s', $snapshotApproval->status->value)) }}<br>
    </p>
</div>
</x-mail::message>
