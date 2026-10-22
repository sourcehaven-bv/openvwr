@php
    $statePath = $getStatePath();
    $columns = $getTableColumns();
    $records = $getLinkedRecords();
    $isDisabled = $isDisabled();

    $prefixActions = $getPrefixActions();
    $suffixActions = $getSuffixActions();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="fi-relation-table space-y-3">
        {{-- Linked records, rendered as a compact table with a per-row remove button. --}}
        @if ($records->isNotEmpty())
            <div class="overflow-x-auto rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            @foreach ($columns as $column)
                                <th class="px-3 py-2 text-start text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                            @unless ($isDisabled)
                                <th class="w-10 px-3 py-2"></th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($records as $record)
                            <tr wire:key="{{ $statePath }}.row.{{ $record->getKey() }}">
                                @foreach ($columns as $column)
                                    @php
                                        $value = $column['get']($record);
                                        $href = isset($column['href']) ? $column['href']($record) : null;
                                        $download = isset($column['download']) ? $column['download']($record) : null;
                                    @endphp
                                    <td class="px-3 py-2 text-sm text-gray-950 dark:text-white">
                                        @if (filled($value) && filled($href))
                                            <a
                                                href="{{ $href }}"
                                                class="fi-link text-primary-600 hover:underline dark:text-primary-400"
                                            >{{ $value }}</a>
                                        @else
                                            {{ $value }}
                                        @endif
                                        @if (filled($download))
                                            <a
                                                href="{{ $download }}"
                                                download
                                                title="{{ __('general.download') }}"
                                                class="ms-1 inline-flex rounded-md p-1 align-middle text-gray-400 transition hover:bg-gray-50 hover:text-primary-600 dark:hover:bg-white/5 dark:hover:text-primary-400"
                                            >
                                                <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                                                <span class="sr-only">{{ __('general.download') }}</span>
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                                @unless ($isDisabled)
                                    <td class="px-3 py-2 text-end">
                                        <button
                                            type="button"
                                            title="{{ __('general.delete') }}"
                                            wire:click="mountFormComponentAction(@js($statePath), @js(\App\Filament\Forms\Components\RelationTable::REMOVE_ACTION), { id: @js((string) $record->getKey()) })"
                                            wire:loading.attr="disabled"
                                            class="rounded-md p-1 text-gray-400 transition hover:bg-gray-50 hover:text-danger-600 dark:hover:bg-white/5 dark:hover:text-danger-400"
                                        >
                                            <x-heroicon-m-x-mark class="h-5 w-5" />
                                            <span class="sr-only">{{ __('general.delete') }}</span>
                                        </button>
                                    </td>
                                @endunless
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Searchable "add" control. This is the standard Filament select input,
             entangled to the same state path as the table above, so
             linking a record adds a row and the inline "create option" action still
             works. Only the input control is rendered here — the label lives on the
             surrounding field wrapper. --}}
        @unless ($isDisabled)
            {{-- The linked records are already shown in the table above, so the
                 selected-item chips are hidden here; this leaves a clean "add" box. --}}
            <style>
                .fi-relation-table-add .fi-select-input-value-badges-ctn {
                    display: none;
                }
            </style>
            <x-filament::input.wrapper
                :prefix-actions="$prefixActions"
                :suffix-actions="$suffixActions"
                :valid="! $errors->has($statePath)"
                :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                        ->class(['fi-fo-select', 'fi-relation-table-add'])
                "
            >
                {{-- Mirrors the non-native branch of Select::toEmbeddedHtml(). --}}
                <div
                    x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('select', 'filament/forms') }}"
                    x-data="selectFormComponent({
                                canOptionLabelsWrap: @js($canOptionLabelsWrap()),
                                canSelectPlaceholder: @js($canSelectPlaceholder()),
                                clearButtonLabel: @js(__('filament-forms::components.select.actions.clear.label')),
                                errorMessage: @js(__('filament-forms::components.select.error_message')),
                                getOptionLabelUsing: async () => {
                                    return await Livewire.fireAction($wire.__instance, 'callSchemaComponentMethod', [@js($getKey()), 'getOptionLabel'], { async: true })
                                },
                                getOptionLabelsUsing: async () => {
                                    return await Livewire.fireAction($wire.__instance, 'callSchemaComponentMethod', [@js($getKey()), 'getOptionLabelsForJs'], { async: true })
                                },
                                getOptionsUsing: async () => {
                                    return await Livewire.fireAction($wire.__instance, 'callSchemaComponentMethod', [@js($getKey()), 'getOptionsForJs'], { async: true })
                                },
                                getSearchResultsUsing: async (search) => {
                                    return await Livewire.fireAction($wire.__instance, 'callSchemaComponentMethod', [@js($getKey()), 'getSearchResultsForJs', { search }], { async: true })
                                },
                                hasDynamicOptions: @js($hasDynamicOptions()),
                                hasDynamicSearchResults: @js($hasDynamicSearchResults()),
                                hasInitialNoOptionsMessage: false,
                                id: @js($getId()),
                                initialOptionLabel: null,
                                initialOptionLabels: [],
                                initialState: @js($getState()),
                                isAutofocused: @js($isAutofocused()),
                                isDisabled: false,
                                isHtmlAllowed: @js($isHtmlAllowed()),
                                isMultiple: @js($isMultiple()),
                                isReorderable: false,
                                isSearchable: @js($isSearchable()),
                                livewireId: @js($this->getId()),
                                loadingMessage: @js($getLoadingMessage()),
                                maxItems: @js($getMaxItems()),
                                maxItemsMessage: @js($getMaxItemsMessage()),
                                noOptionsMessage: @js($getNoOptionsMessage()),
                                noSearchResultsMessage: @js($getNoSearchResultsMessage()),
                                options: @js($getOptionsForJs()),
                                optionsLimit: @js($getOptionsLimit()),
                                placeholder: @js($getPlaceholder()),
                                position: @js($getPosition()),
                                removeButtonLabel: @js(__('filament-forms::components.select.actions.remove_option.label')),
                                searchDebounce: @js($getSearchDebounce()),
                                searchingMessage: @js($getSearchingMessage()),
                                searchLabel: @js(__('filament-forms::components.select.search_label')),
                                searchPrompt: @js($getSearchPrompt()),
                                searchableOptionFields: @js($getSearchableOptionFields()),
                                state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                                statePath: @js($statePath),
                            })"
                    wire:ignore
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.add"
                    class="fi-select-input"
                >
                    <div x-ref="select"></div>
                </div>
            </x-filament::input.wrapper>
        @endunless
    </div>
</x-dynamic-component>
