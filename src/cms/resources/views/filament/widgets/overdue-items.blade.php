<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('dashboard.overdue.heading')"
        :description="__('dashboard.overdue.description')"
        icon="heroicon-o-exclamation-triangle"
        icon-color="danger"
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
                                {{ $row['type'] }}
                            </span>
                        </span>

                        <span class="flex items-center gap-x-3 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ $row['kind'] }}</span>

                            <x-filament::badge color="danger">
                                {{ $row['date'] }}
                            </x-filament::badge>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
