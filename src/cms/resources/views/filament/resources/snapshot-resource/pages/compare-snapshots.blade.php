<x-filament-panels::page>
    @php
        $options = $this->getVersionOptions();
        $diffs = $this->getDiffs();
        $fromId = $this->fromId;
        $toId = $this->toId;
    @endphp

    <x-filament::section>
        <div class="snapshot-diff">
            <table class="snapshot-diff-pickers">
                <colgroup>
                    <col style="width: 50%;">
                    <col style="width: 50%;">
                </colgroup>
                <tr>
                    <th>
                        <label for="compare-from" class="snapshot-diff-picker-label">
                            {{ __('snapshot.compare_from') }}
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="fromId" id="compare-from">
                                @foreach ($options as $id => $label)
                                    <option value="{{ $id }}" @selected($id === $fromId)>{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </th>
                    <th>
                        <label for="compare-to" class="snapshot-diff-picker-label">
                            {{ __('snapshot.compare_to') }}
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="toId" id="compare-to">
                                @foreach ($options as $id => $label)
                                    <option value="{{ $id }}" @selected($id === $toId)>{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </th>
                </tr>
            </table>
        </div>
    </x-filament::section>

    @foreach ($diffs as $section => $diff)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('snapshot.' . $section . '_data') }}
            </x-slot>

            <div class="snapshot-diff">
                {{ $diff }}
            </div>
        </x-filament::section>
    @endforeach

    @push('styles')
        <style>
            /* Picker row mirrors the diff table geometry: two flush 50% columns,
               so "Oude versie" caps the old column and "Nieuwe versie" the new. */
            .snapshot-diff-pickers {
                width: 100%;
                table-layout: fixed;
                border-collapse: collapse;
            }
            .snapshot-diff-pickers th {
                padding: 0 0.5rem;
                text-align: left;
                vertical-align: bottom;
                font-weight: inherit;
            }
            .snapshot-diff-pickers th:first-child { padding-left: 0; }
            .snapshot-diff-pickers th:last-child { padding-right: 0; }
            .snapshot-diff-picker-label {
                display: block;
                margin-bottom: 0.25rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: rgb(3 7 18); /* gray-950 */
            }
            .dark .snapshot-diff-picker-label { color: rgb(255 255 255); }

            .snapshot-diff { overflow-x: auto; }
            .snapshot-diff table.diff {
                width: 100%;
                border-collapse: collapse;
                font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                font-size: 0.8125rem;
                line-height: 1.5;
            }
            .snapshot-diff table.diff td {
                padding: 0.125rem 0.5rem;
                vertical-align: top;
                white-space: pre-wrap;
                word-break: break-word;
                width: 50%;
                border: 1px solid rgb(229 231 235); /* gray-200 */
            }
            .snapshot-diff table.diff thead th {
                padding: 0.25rem 0.5rem;
                text-align: left;
                font-weight: 600;
                border: 1px solid rgb(229 231 235);
            }
            .snapshot-diff td.none { background-color: rgb(249 250 251); } /* gray-50 */
            .snapshot-diff .change-ins td.new,
            .snapshot-diff .change-rep td.new { background-color: rgb(220 252 231); } /* green-100 */
            .snapshot-diff .change-del td.old,
            .snapshot-diff .change-rep td.old { background-color: rgb(254 226 226); } /* red-100 */
            .snapshot-diff ins { background-color: rgb(134 239 172); text-decoration: none; } /* green-300 */
            .snapshot-diff del { background-color: rgb(252 165 165); text-decoration: none; } /* red-300 */
            .snapshot-diff tbody.skipped td { background-color: rgb(249 250 251); height: 0.5rem; }
            .snapshot-diff-empty {
                padding: 0.75rem;
                color: rgb(107 114 128); /* gray-500 */
                font-style: italic;
            }

            .dark .snapshot-diff table.diff td,
            .dark .snapshot-diff table.diff thead th { border-color: rgb(55 65 81); } /* gray-700 */
            .dark .snapshot-diff td.none { background-color: rgb(31 41 55); } /* gray-800 */
            .dark .snapshot-diff .change-ins td.new,
            .dark .snapshot-diff .change-rep td.new { background-color: rgba(21 128 61 / 0.35); } /* green-700 */
            .dark .snapshot-diff .change-del td.old,
            .dark .snapshot-diff .change-rep td.old { background-color: rgba(185 28 28 / 0.35); } /* red-700 */
            .dark .snapshot-diff ins { background-color: rgba(21 128 61 / 0.65); color: rgb(240 253 244); }
            .dark .snapshot-diff del { background-color: rgba(185 28 28 / 0.65); color: rgb(254 242 242); }
            .dark .snapshot-diff tbody.skipped td { background-color: rgb(31 41 55); }
            .dark .snapshot-diff-empty { color: rgb(156 163 175); } /* gray-400 */
        </style>
    @endpush
</x-filament-panels::page>
