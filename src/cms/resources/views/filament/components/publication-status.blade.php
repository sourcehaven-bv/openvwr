@php
    /** @var \App\Services\StaticWebsite\Publication\PublicationStatus $status */
    /** @var list<string> $history */
    $notice = $status->getVersionInProgressNotice();
@endphp
<div class="space-y-3 text-sm">
    <div class="flex flex-wrap items-center gap-2">
        <x-filament::badge :color="$status->state->getColor()->value" :icon="$status->state->getIcon()">
            {{ $status->state->getLabel() }}
        </x-filament::badge>
        @if($status->url !== null)
            <x-filament::link :href="$status->url" target="_blank" rel="noopener noreferrer" icon="heroicon-m-arrow-top-right-on-square" size="sm">
                {{ __('general.go_to_public_page') }}
            </x-filament::link>
        @endif
    </div>

    <p>{{ $status->getExplanation() }}</p>

    @if($notice !== null)
        <p class="text-gray-600 dark:text-gray-400">{{ $notice }}</p>
    @endif

    @if($history !== [])
        <div>
            <p class="font-medium">{{ __('static_website.public_from_section.public_history') }}</p>
            <ul class="list-disc list-inside">
                @foreach($history as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
