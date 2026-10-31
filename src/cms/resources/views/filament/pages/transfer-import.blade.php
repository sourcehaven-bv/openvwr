<x-filament-panels::page>
    @if ($bundlePath === null)
        {{ __('transfer.import_help') }}
        <x-form wire:submit="analyse">
            {{ $this->form }}
            <div>
                <x-filament::button type="submit" size="sm">
                    {{ __('transfer.analyse') }}
                </x-filament::button>
            </div>
        </x-form>
    @else
        <x-filament::section>
            <x-slot name="heading">{{ __('transfer.preview_heading') }}</x-slot>
            <x-slot name="description">
                {{ __('transfer.preview_source', ['organisation' => $sourceOrganisation, 'date' => $exportedAt]) }}
            </x-slot>

            <div class="space-y-6">
                @foreach ($this->groupedItems() as $typeLabel => $groupItems)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-2">
                            {{ $typeLabel }}
                        </h3>
                        <ul class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($groupItems as $id => $item)
                                <li class="flex items-center gap-4 py-2" wire:key="transfer-item-{{ $id }}">
                                    <label class="flex items-center gap-3 flex-1 min-w-0">
                                        <x-filament::input.checkbox wire:model="items.{{ $id }}.selected" />
                                        <span class="truncate text-sm text-gray-950 dark:text-white">
                                            {{ $item['name'] }}
                                        </span>
                                    </label>

                                    @if ($item['has_match'])
                                        @if ($item['needs_decision'])
                                            <x-filament::badge color="warning">
                                                {{ __('transfer.exists_edited', ['name' => $item['match_name']]) }}
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray">
                                                {{ __('transfer.exists_unchanged', ['name' => $item['match_name']]) }}
                                            </x-filament::badge>
                                        @endif

                                        <div class="w-56 shrink-0">
                                            <x-filament::input.wrapper>
                                                <x-filament::input.select wire:model="items.{{ $id }}.strategy">
                                                    <option value="skip">{{ __('transfer.strategy_skip') }}</option>
                                                    <option value="overwrite">{{ __('transfer.strategy_overwrite') }}</option>
                                                    <option value="copy">{{ __('transfer.strategy_copy') }}</option>
                                                </x-filament::input.select>
                                            </x-filament::input.wrapper>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                {{ __('transfer.lookup_note') }}
            </p>

            <div class="mt-6 flex gap-3">
                <x-filament::button wire:click="import">
                    {{ __('transfer.import_submit') }}
                </x-filament::button>
                <x-filament::button color="gray" wire:click="cancel">
                    {{ __('transfer.cancel') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
