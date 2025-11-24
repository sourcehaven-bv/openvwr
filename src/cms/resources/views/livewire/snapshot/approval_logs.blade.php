@php use App\Enums\Snapshot\SnapshotApprovalLogMessageType; @endphp
<div>
    @if($snapshot->snapshotApprovalLogs->isNotEmpty())
        <section
            class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content p-6">
                    <ol class="relative border-s border-gray-200 dark:border-gray-700">
                        @foreach($snapshot->snapshotApprovalLogs as $state)
                            <li class="mb-10 ms-4">
                                <div @class([
                                    'absolute w-3 h-3 rounded-full mt-1.5 -start-1.5 border border-white dark:border-gray-900',
                                    'bg-gray-700 dark:bg-white' => $state->message['type'] === SnapshotApprovalLogMessageType::APPROVAL_UPDATE->value,
                                    'bg-gray-200 dark:bg-gray-700' => $state->message['type'] !== SnapshotApprovalLogMessageType::APPROVAL_UPDATE->value,
                                ])></div>
                                <time @class([
                                    'mb-1 text-sm font-normal leading-none',
                                    'text-gray-900 dark:text-white' => $state->message['type'] === SnapshotApprovalLogMessageType::APPROVAL_UPDATE->value,
                                    'text-gray-400 dark:text-gray-500' => $state->message['type'] !== SnapshotApprovalLogMessageType::APPROVAL_UPDATE->value,
                                ])>
                                    {{ DateFormat::toDateTime($state->created_at) }} - {{ $state->user->name }}
                                </time>
                                @if ($state->message['type'] === SnapshotApprovalLogMessageType::APPROVAL_UPDATE->value)
                                    <h3 class="text-lg font-semibold">
                                        {{ __(sprintf('snapshot_approval_status.%s', $state->message['status'])) }}
                                        <p class="text-sm font-medium">
                                            {{ $state->message['notes'] }}
                                        </p>
                                    </h3>
                                @else
                                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">
                                        {{ __(sprintf('snapshot_approval_log.%s', $state->message['type'])) }}
                                        voor: {{ $state->message['assigned_to'] }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>
    @endif
</div>
