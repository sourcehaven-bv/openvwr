<x-filament-panels::page>
    @php($overview = $this->getPublicationOverview())
    <x-filament::section
        :heading="__('public_website.overview.title')"
        :description="__('public_website.overview.subtitle')"
    >
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-medium">{{ __('public_website.overview.checked_at') }}</dt>
                <dd>{{ $overview['checked_at'] ?? __('public_website.overview.never_checked') }}</dd>
            </div>
            <div>
                <dt class="font-medium">{{ __('public_website.overview.confirmed') }}</dt>
                <dd>{{ $overview['confirmed'] }}</dd>
            </div>
        </dl>

        <div class="mt-4 text-sm">
            @if($overview['unconfirmed'] === [])
                <p>{{ __('public_website.overview.all_confirmed') }}</p>
            @else
                <p class="font-medium">{{ __('public_website.overview.unconfirmed') }}</p>
                <ul class="mt-2 space-y-2">
                    @foreach($overview['unconfirmed'] as $item)
                        <li class="flex flex-wrap items-center gap-2">
                            <x-filament::badge :color="$item['status']->state->getColor()->value" :icon="$item['status']->state->getIcon()">
                                {{ $item['status']->state->getLabel() }}
                            </x-filament::badge>
                            <span>{{ $item['record']->getPublicIdentifier() }} {{ $item['record']->getDisplayName() }} ({{ $item['record']->getOrganisation()->name }})</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $item['status']->getExplanation() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-filament::section>

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
