@php
    use Filament\Support\Facades\FilamentView;

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
                                    @endphp
                                    <td class="px-3 py-2 text-sm text-gray-950 dark:text-white">
                                        @if (filled($value) && filled($href))
                                            <a
                                                href="{{ $href }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="fi-link inline-flex items-center gap-1 text-primary-600 hover:underline dark:text-primary-400"
                                            >
                                                {{ $value }}
                                                <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                                            </a>
                                        @else
                                            {{ $value }}
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

        {{-- Searchable "add" control. This is the standard Filament select input
             (choices.js), entangled to the same state path as the table above, so
             linking a record adds a row and the inline "create option" action still
             works. Only the input control is rendered here — the label lives on the
             surrounding field wrapper. --}}
        @unless ($isDisabled)
            {{-- The linked records are already shown in the table above, so the
                 selected-item chips are hidden here; this leaves a clean "add" box. --}}
            <style>
                .fi-relation-table-add .choices__list--multiple {
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
                <div
                    @if (FilamentView::hasSpaMode())
                        {{-- format-ignore-start --}}x-load="visible || event (ax-modal-opened)"{{-- format-ignore-end --}}
                    @else
                        x-load
                    @endif
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('select', 'filament/forms') }}"
                    x-data="selectFormComponent({
                                canSelectPlaceholder: @js($canSelectPlaceholder()),
                                isHtmlAllowed: @js($isHtmlAllowed()),
                                getOptionLabelUsing: async () => {
                                    return await $wire.getFormSelectOptionLabel(@js($statePath))
                                },
                                getOptionLabelsUsing: async () => {
                                    return await $wire.getFormSelectOptionLabels(@js($statePath))
                                },
                                getOptionsUsing: async () => {
                                    return await $wire.getFormSelectOptions(@js($statePath))
                                },
                                getSearchResultsUsing: async (search) => {
                                    return await $wire.getFormSelectSearchResults(@js($statePath), search)
                                },
                                isAutofocused: @js($isAutofocused()),
                                isMultiple: @js($isMultiple()),
                                isSearchable: @js($isSearchable()),
                                livewireId: @js($this->getId()),
                                hasDynamicOptions: @js($hasDynamicOptions()),
                                hasDynamicSearchResults: @js($hasDynamicSearchResults()),
                                loadingMessage: @js($getLoadingMessage()),
                                maxItems: @js($getMaxItems()),
                                maxItemsMessage: @js($getMaxItemsMessage()),
                                noSearchResultsMessage: @js($getNoSearchResultsMessage()),
                                options: @js($getOptionsForJs()),
                                optionsLimit: @js($getOptionsLimit()),
                                placeholder: @js($getPlaceholder()),
                                position: @js($getPosition()),
                                searchDebounce: @js($getSearchDebounce()),
                                searchingMessage: @js($getSearchingMessage()),
                                searchPrompt: @js($getSearchPrompt()),
                                searchableOptionFields: @js($getSearchableOptionFields()),
                                state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                                statePath: @js($statePath),
                            })"
                    wire:ignore
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.add"
                    x-on:keydown.esc="select.dropdown.isActive && $event.stopPropagation()"
                >
                    <select
                        x-ref="input"
                        {{
                            $getExtraInputAttributeBag()
                                ->merge([
                                    'id' => $getId(),
                                    'multiple' => $isMultiple(),
                                ], escape: false)
                                ->class([
                                    'h-9 w-full rounded-lg border-none bg-transparent !bg-none',
                                ])
                        }}
                    ></select>
                </div>
            </x-filament::input.wrapper>
        @endunless
    </div>
</x-dynamic-component>
