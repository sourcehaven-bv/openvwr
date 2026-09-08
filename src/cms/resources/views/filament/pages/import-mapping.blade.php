<x-filament-panels::page>
    @if ($step === \App\Filament\Pages\ImportMapping::STEP_UPLOAD)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('import_mapping.help') }}
        </p>

        <x-filament-panels::form wire:submit="analyse">
            {{ $this->form }}
        </x-filament-panels::form>
    @endif

    @if ($step === \App\Filament\Pages\ImportMapping::STEP_ARCHIVE)
        <x-filament::section>
            <x-slot name="heading">{{ __('import_mapping.archive_heading') }}</x-slot>
            <x-slot name="description">{{ __('import_mapping.archive_body') }}</x-slot>

            <ul class="space-y-1">
                @foreach ($result['archive'] as $register => $count)
                    <li class="text-sm">
                        &bull; {{ __('import_mapping.archive_row', ['register' => $register, 'count' => $count]) }}
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        <div class="flex gap-3">
            <x-filament::button wire:click="applyArchive" size="sm">
                {{ __('import_mapping.archive_apply') }}
            </x-filament::button>

            <x-filament::button wire:click="restart" color="danger" size="sm" outlined>
                {{ __('import_mapping.restart') }}
            </x-filament::button>
        </div>
    @endif

    @if ($step === \App\Filament\Pages\ImportMapping::STEP_REVIEW)
        @php
            $review = $this->review();
            $unsettled = $review->unsettledHeaders();
            $settled = $review->settledHeaders();
            $groupedOptions = $review->options()->grouped();
        @endphp

        @if ($recognisedProfile)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.recognised_heading') }}</x-slot>
                {{ __('import_mapping.recognised_body', ['name' => $recognisedProfile]) }}
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ __('import_mapping.rows_heading') }}</x-slot>
            <x-slot name="description">
                @if ($review->identity() !== null)
                    {{ trans_choice('import_mapping.rows_proposal', $review->recordCount(), ['rows' => $review->rowCount(), 'records' => $review->recordCount(), 'column' => $review->identity()]) }}
                @else
                    {{ __('import_mapping.rows_body') }}
                @endif
            </x-slot>

            <label class="block">
                <span class="block text-xs font-medium mb-1">{{ __('import_mapping.rows_column') }}</span>
                <select
                    wire:model.live="groupBy"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
                >
                    <option value="">{{ __('import_mapping.rows_none') }}</option>
                    @foreach ($headers as $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </select>
            </label>

            @if ($review->identity() !== null)
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('import_mapping.rows_effect', ['records' => $review->recordCount(), 'rows' => $review->rowCount()]) }}
                </p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('import_mapping.review_heading') }}</x-slot>
            <x-slot name="description">
                {{ __('import_mapping.review_body', ['rows' => $review->rowCount(), 'target' => \App\Enums\Import\ImportTarget::from($target)->label()]) }}
            </x-slot>

            @if (count($unsettled) === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('import_mapping.all_settled') }}
                </p>
            @endif

            <div class="space-y-4">
                @foreach ($unsettled as $header)
                    @php($column = $review->column($header))
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                            <span class="font-mono text-sm font-medium">{{ $header }}</span>
                            <span @class([
                                'text-xs',
                                'text-warning-600 dark:text-warning-400' => $column->needsAttention(),
                                'text-gray-500 dark:text-gray-400' => !$column->needsAttention(),
                            ])>
                                {{ $column->statusLabel() }}
                            </span>
                        </div>

                        @php($samples = $column->samples())
                        @if (count($samples) > 0)
                            <ul class="mb-3 space-y-1">
                                @foreach ($samples as $sample)
                                    <li class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        <span class="text-gray-400 dark:text-gray-500">&bull;</span> {{ $sample }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div>
                            <label class="block">
                                <span class="block text-xs font-medium mb-1">
                                    {{ __('import_mapping.column_target') }}
                                </span>
                                <select
                                    wire:model.live="mapping.{{ $header }}.target"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
                                >
                                    @foreach ($groupedOptions as $group => $options)
                                        @if ($group === __('import_mapping.group_none'))
                                            @foreach ($options as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        @else
                                            <optgroup label="{{ $group }}">
                                                @foreach ($options as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </label>

                            @php($transform = $column->transformLabel())
                            @if ($transform !== '')
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('import_mapping.read_as', ['transform' => $transform]) }}
                                </p>
                            @endif

                            @if ($column->needsTrueDate())
                                <div class="mt-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3">
                                    <p class="text-xs mb-2">{{ __('import_mapping.true_date_intro') }}</p>

                                    <div class="flex flex-wrap items-center gap-3">
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                type="radio"
                                                value="today"
                                                wire:model.live="mapping.{{ $header }}.true_date_mode"
                                            />
                                            {{ __('import_mapping.true_date_today') }}
                                        </label>

                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                type="radio"
                                                value="fixed"
                                                wire:model.live="mapping.{{ $header }}.true_date_mode"
                                            />
                                            {{ __('import_mapping.true_date_fixed') }}
                                        </label>

                                        @if (($mapping[$header]['true_date_mode'] ?? 'today') === 'fixed')
                                            <input
                                                type="date"
                                                wire:model="mapping.{{ $header }}.true_date"
                                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-xs"
                                            />
                                        @endif
                                    </div>

                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('import_mapping.true_date_no') }}
                                    </p>
                                </div>
                            @endif

                            @php($dateExamples = count($column->dateFormatCandidates()) > 1 ? $column->dateFormatExamples() : [])
                            @if (count($dateExamples) > 1)
                                <div class="mt-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3">
                                    <p class="text-xs mb-2">
                                        {{ __('import_mapping.date_format_intro', ['sample' => $column->samples(1)[0] ?? '']) }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-3">
                                        @foreach ($dateExamples as $format => $example)
                                            <label class="flex items-center gap-2 text-xs">
                                                <input
                                                    type="radio"
                                                    value="{{ $format }}"
                                                    wire:model.live="mapping.{{ $header }}.date_format"
                                                />
                                                {{ $example }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($column->dateFormat() !== null && ($column->samples(1)[0] ?? '') !== '')
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('import_mapping.date_format_example', ['sample' => $column->samples(1)[0], 'date' => $column->dateFormatExamples()[$column->dateFormat()] ?? '']) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        @if (count($settled) > 0)
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">
                    {{ __('import_mapping.settled_heading', ['count' => count($settled)]) }}
                </x-slot>
                <x-slot name="description">
                    {{ __('import_mapping.settled_body') }}
                </x-slot>

                <div class="space-y-4">
                    @foreach ($settled as $header)
                        @php($column = $review->column($header))
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-2">
                                <span class="font-mono text-sm font-medium">{{ $header }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    &rarr; {{ $column->targetLabel() }}
                                </span>
                            </div>

                            @php($samples = $column->samples())
                            @if (count($samples) > 0)
                                <ul class="mb-3 space-y-1">
                                    @foreach ($samples as $sample)
                                        <li class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            <span class="text-gray-400 dark:text-gray-500">&bull;</span> {{ $sample }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <div>
                                <label class="block">
                                    <span class="block text-xs font-medium mb-1">
                                        {{ __('import_mapping.column_target') }}
                                    </span>
                                    <select
                                        wire:model.live="mapping.{{ $header }}.target"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
                                    >
                                        @foreach ($groupedOptions as $group => $options)
                                            @if ($group === __('import_mapping.group_none'))
                                                @foreach ($options as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            @else
                                                <optgroup label="{{ $group }}">
                                                    @foreach ($options as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        @if ($result['fits'] > 0 || count($result['issues']) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('import_mapping.dry_run_heading') }}
                </x-slot>

                <p class="text-sm mb-3">
                    {{ __('import_mapping.dry_run_summary', ['fits' => $result['fits'], 'issues' => count($result['issues'])]) }}
                </p>

                @if (count($result['issues']) > 0)
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 pr-4 font-medium">{{ __('import_mapping.column_row') }}</th>
                                <th class="py-2 font-medium">{{ __('import_mapping.column_reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['issues'] as $issue)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2 pr-4">{{ $issue['row'] }}</td>
                                    <td class="py-2">{{ $issue['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ __('import_mapping.save_profile_heading') }}</x-slot>

            <input
                type="text"
                wire:model="profileName"
                placeholder="{{ __('import_mapping.save_profile_placeholder') }}"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
            />
        </x-filament::section>

        <div class="flex gap-3">
            <x-filament::button wire:click="dryRun" color="gray" size="sm">
                {{ __('import_mapping.dry_run') }}
            </x-filament::button>

            <x-filament::button wire:click="apply" size="sm">
                {{ __('import_mapping.apply') }}
            </x-filament::button>

            <x-filament::button wire:click="restart" color="danger" size="sm" outlined>
                {{ __('import_mapping.restart') }}
            </x-filament::button>
        </div>
    @endif

    @if ($step === \App\Filament\Pages\ImportMapping::STEP_RESULT)
        @php($entities = $result['entities'])

        <x-filament::section>
            <x-slot name="heading">{{ __('import_mapping.result_heading') }}</x-slot>

            <p class="text-sm">
                {{ __('import_mapping.result_body', ['count' => $result['imported'], 'issues' => count($result['issues'])]) }}
            </p>

            @if ($result['skipped'] > 0)
                <p class="text-sm mt-2">
                    {{ __('import_mapping.skipped_existing', ['count' => $result['skipped']]) }}
                </p>
            @endif

            @if (count($result['failures']) > 0)
                <p class="text-sm mt-2 text-warning-600 dark:text-warning-400">
                    {{ __('import_mapping.result_failed', ['count' => count($result['failures'])]) }}
                </p>
            @endif
        </x-filament::section>

        @if (count($result['failures']) > 0)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.failed_rows_heading') }}</x-slot>
                <x-slot name="description">{{ __('import_mapping.failed_rows_body') }}</x-slot>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-medium">{{ __('import_mapping.column_row') }}</th>
                            <th class="py-2 font-medium">{{ __('import_mapping.column_reason') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($result['failures'] as $failure)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 pr-4">{{ $failure['row'] }}</td>
                                <td class="py-2">{{ $failure['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-filament::section>
        @endif

        @if (count($entities['new']) > 0)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.new_entities_heading') }}</x-slot>
                <x-slot name="description">{{ __('import_mapping.new_entities_body') }}</x-slot>

                @foreach ($entities['new'] as $names)
                    <ul class="space-y-1">
                        @foreach (array_unique($names) as $name)
                            <li class="text-sm">&bull; {{ $name }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </x-filament::section>
        @endif

        @if (count($entities['unresolved']) > 0)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.unresolved_entities_heading') }}</x-slot>
                <x-slot name="description">{{ __('import_mapping.unresolved_entities_body') }}</x-slot>

                @foreach ($entities['unresolved'] as $names)
                    <ul class="space-y-1">
                        @foreach (array_unique($names) as $name)
                            <li class="text-sm">&bull; {{ $name }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </x-filament::section>
        @endif

        @if (count($entities['ambiguous']) > 0)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.ambiguous_entities_heading') }}</x-slot>
                <x-slot name="description">{{ __('import_mapping.ambiguous_entities_body') }}</x-slot>

                @foreach ($entities['ambiguous'] as $names)
                    <ul class="space-y-1">
                        @foreach ($names as $name => $count)
                            <li class="text-sm">
                                {{ __('import_mapping.ambiguous_entity', ['name' => $name, 'count' => $count]) }}
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </x-filament::section>
        @endif

        @if (count($entities['fuzzy']) > 0)
            <x-filament::section>
                <x-slot name="heading">{{ __('import_mapping.fuzzy_entities_heading') }}</x-slot>

                @foreach ($entities['fuzzy'] as $matches)
                    <ul class="space-y-1">
                        @foreach ($matches as $match)
                            <li class="text-sm">
                                {{ __('import_mapping.fuzzy_entities_body', $match) }}
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </x-filament::section>
        @endif

        <div>
            <x-filament::button wire:click="restart" size="sm">
                {{ __('import_mapping.restart') }}
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
