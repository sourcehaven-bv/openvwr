<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('dashboard.approvals.heading')"
        :description="__('dashboard.approvals.description')"
        icon="heroicon-o-clipboard-document-check"
    >
        <ul role="list" class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($this->getRows() as $row)
                <li class="py-3 first:pt-0 last:pb-0">
                    <a
                        href="{{ $row['url'] }}"
                        class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 hover:underline"
                    >
                        <span class="flex flex-col gap-y-0.5">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                {{ $row['name'] }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('snapshot.model_singular') }}
                            </span>
                        </span>

                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $row['requested'] }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($this->hasMore())
            <x-slot name="footerActions">
                <x-filament::link :href="$this->getAllUrl()" size="sm">
                    {{ __('dashboard.show_all') }}
                </x-filament::link>
            </x-slot>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
