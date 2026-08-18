@php
    use App\Services\ApReport\AnswerSource;

    $report = $this->getReport();
@endphp

<x-filament-panels::page>
    <div class="space-y-6 text-sm">

        <div class="space-y-2 text-gray-600 dark:text-gray-400">
            <p>{{ __('ap_report.intro') }}</p>
            <p>
                <a href="https://datalekken.autoriteitpersoonsgegevens.nl"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="text-primary-600 hover:underline dark:text-primary-400"
                >
                    https://datalekken.autoriteitpersoonsgegevens.nl
                </a>
            </p>
        </div>

        <x-filament::section>
            <x-slot name="heading">{{ __('ap_report.summary_title') }}</x-slot>

            <h3 class="font-semibold">
                {{ __('ap_report.summary_missing', ['count' => $report->missingCount()]) }}
            </h3>

            @if ($report->missingCount() === 0)
                <p class="mt-1">{{ __('ap_report.summary_missing_empty') }}</p>
            @else
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($report->missingAnswers() as $answer)
                        <li>
                            <span class="font-mono text-xs">{{ $answer->number }}</span>
                            {{ $answer->label }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($report->needsConfirmationCount() > 0)
                <h3 class="mt-4 font-semibold">
                    {{ __('ap_report.summary_confirm', ['count' => $report->needsConfirmationCount()]) }}
                </h3>
                <p class="mt-1">{{ __('ap_report.summary_confirm_explanation') }}</p>
            @endif
        </x-filament::section>

        @foreach ($report->chapters as $chapter)
            <x-filament::section>
                <x-slot name="heading">{{ $chapter->number }}. {{ $chapter->title }}</x-slot>

                <div class="space-y-3">
                    @foreach ($chapter->answers as $answer)
                        @php
                            $tone = match ($answer->source) {
                                AnswerSource::DERIVED => 'border-warning-400 bg-warning-50 dark:bg-warning-500/10',
                                AnswerSource::MISSING => 'border-danger-400 bg-danger-50 dark:bg-danger-500/10',
                                AnswerSource::RECORDED => 'border-gray-200 dark:border-gray-700',
                            };
                        @endphp

                        <div class="border-l-4 py-1 pl-3 {{ $tone }}">
                            <p class="font-semibold">
                                <span class="font-mono text-xs text-gray-500">{{ $answer->number }}</span>
                                {{ $answer->label }}
                            </p>

                            @if ($answer->isMissing())
                                <p class="italic text-danger-700 dark:text-danger-400">
                                    {{ __('ap_report.not_recorded') }}
                                </p>

                                @if ($answer->hints !== [])
                                    <p class="mt-0.5 text-xs text-warning-700 dark:text-warning-400">
                                        {{ __('ap_report.hint_prefix') }}:
                                        {{ implode(', ', $answer->hints) }}.
                                        {{ __('ap_report.hint_explanation') }}
                                        @if ($answer->origins !== [])
                                            ({{ __('ap_report.origin_prefix') }}:
                                            {{ implode(', ', $answer->origins) }})
                                        @endif
                                    </p>
                                @endif
                            @elseif ($answer->isMultiValued())
                                <ul class="list-disc space-y-0.5 pl-5">
                                    @foreach ($answer->values as $value)
                                        <li>{{ $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="whitespace-pre-line">{{ $answer->values[0] }}</p>
                            @endif

                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                @switch($answer->source)
                                    @case(AnswerSource::RECORDED)
                                        {{ __('ap_report.source_recorded') }}
                                        @break
                                    @case(AnswerSource::DERIVED)
                                        {{ __('ap_report.source_derived') }}
                                        @if ($answer->origins !== [])
                                            &mdash;
                                            {{ __('ap_report.origin_prefix') }}:
                                            {{ implode(', ', $answer->origins) }}
                                        @endif
                                        @break
                                    @default
                                        {{ __('ap_report.source_missing') }}
                                @endswitch
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
