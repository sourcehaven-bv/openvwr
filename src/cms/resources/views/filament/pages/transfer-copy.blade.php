<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('transfer.copy_target_heading') }}</x-slot>
        <x-slot name="description">{{ __('transfer.copy_target_description') }}</x-slot>

        @php
            $targetOptions = $this->targetOptions();
        @endphp

        @if (count($targetOptions) === 0)
            <x-filament::badge color="warning">
                {{ __('transfer.copy_no_targets') }}
            </x-filament::badge>
        @else
            <div class="max-w-md">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="targetOrganisationId" :disabled="$analysed">
                        <option value="">{{ __('transfer.copy_pick_target') }}</option>
                        @foreach ($targetOptions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            @unless ($analysed)
                <div class="mt-6 space-y-6">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('transfer.copy_related_heading') }}
                    </h3>

                    @foreach ($this->relatedGroups() as $relationName => $group)
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ $group['type']->label() }}
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($group['options'] as $relatedId => $relatedName)
                                    <label class="flex items-center gap-3">
                                        <x-filament::input.checkbox
                                            wire:model="related.{{ $relationName }}"
                                            value="{{ $relatedId }}"
                                        />
                                        <span class="truncate text-sm text-gray-950 dark:text-white">
                                            {{ $relatedName }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    <x-filament::button wire:click="analyse">
                        {{ __('transfer.copy_analyse') }}
                    </x-filament::button>
                </div>
            @endunless
        @endif
    </x-filament::section>

    @if ($analysed)
        <x-filament::section>
            <x-slot name="heading">{{ __('transfer.copy_preview_heading') }}</x-slot>
            <x-slot name="description">{{ __('transfer.copy_preview_description') }}</x-slot>

            <div class="space-y-6">
                @foreach ($this->groupedItems() as $typeLabel => $groupItems)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-2">{{ $typeLabel }}</h3>
                        <ul class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($groupItems as $id => $item)
                                <li class="flex items-center gap-4 py-2" wire:key="copy-item-{{ $id }}">
                                    <label class="flex items-center gap-3 flex-1 min-w-0">
                                        <x-filament::input.checkbox wire:model="items.{{ $id }}.selected" />
                                        <span class="truncate text-sm text-gray-950 dark:text-white">{{ $item['name'] }}</span>
                                    </label>

                                    @if ($item['needs_decision'])
                                        <x-filament::badge color="warning">
                                            {{ __('transfer.exists_edited', ['name' => $item['match_name']]) }}
                                        </x-filament::badge>

                                        <div class="w-56 shrink-0">
                                            <x-filament::input.wrapper>
                                                <x-filament::input.select wire:model="items.{{ $id }}.strategy">
                                                    <option value="skip">{{ __('transfer.strategy_skip') }}</option>
                                                    <option value="overwrite">{{ __('transfer.strategy_overwrite') }}</option>
                                                    <option value="copy">{{ __('transfer.strategy_copy') }}</option>
                                                </x-filament::input.select>
                                            </x-filament::input.wrapper>
                                        </div>
                                    @elseif ($item['unchanged'])
                                        <span class="text-sm text-gray-500 dark:text-gray-400 shrink-0">
                                            {{ __('transfer.exists_unchanged', ['name' => $item['match_name']]) }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">{{ __('transfer.lookup_note') }}</p>

            @if ($this->allUnchanged())
                <x-filament::badge color="success" class="mt-6">
                    {{ __('transfer.copy_all_unchanged') }}
                </x-filament::badge>
            @endif

            <div class="mt-6 flex gap-3">
                @unless ($this->allUnchanged())
                    <x-filament::button wire:click="copy">{{ __('transfer.copy_submit') }}</x-filament::button>
                @endunless
                <x-filament::button color="gray" wire:click="resetAnalysis">{{ __('transfer.copy_back') }}</x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
