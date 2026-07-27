<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <x-filament::icon
                icon="heroicon-o-check-circle"
                class="h-6 w-6 text-success-500"
            />

            <div>
                @if ($this->hasRegisterAccess())
                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ __('dashboard.all_clear.heading') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('dashboard.all_clear.description') }}
                    </p>
                @else
                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ __('dashboard.all_clear.no_register.heading') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('dashboard.all_clear.no_register.description') }}
                    </p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
