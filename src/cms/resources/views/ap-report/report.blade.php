@php
    use App\Services\ApReport\AnswerSource;
@endphp

<h1>{{ __('ap_report.title') }}</h1>

<p class="intro">{{ __('ap_report.intro') }}</p>
<p class="intro">{{ __('ap_report.portal_hint') }}</p>

<p>
    <b>{{ __('data_breach_record.model_singular') }}</b>:
    {{ $report->dataBreachRecord->name }} ({{ $report->dataBreachRecord->getNumber() }})
</p>

<h2>{{ __('ap_report.summary_title') }}</h2>

<h3>{{ __('ap_report.summary_missing', ['count' => $report->missingCount()]) }}</h3>

@if ($report->missingCount() === 0)
    <p>{{ __('ap_report.summary_missing_empty') }}</p>
@else
    <ul>
        @foreach ($report->missingAnswers() as $answer)
            <li>{{ $answer->number }} &mdash; {{ $answer->label }}</li>
        @endforeach
    </ul>
@endif

@if ($report->needsConfirmationCount() > 0)
    <h3>{{ __('ap_report.summary_confirm', ['count' => $report->needsConfirmationCount()]) }}</h3>
    <p>{{ __('ap_report.summary_confirm_explanation') }}</p>
@endif

@foreach ($report->chapters as $chapter)
    <h2>{{ $chapter->number }}. {{ $chapter->title }}</h2>

    @foreach ($chapter->answers as $answer)
        <div class="answer answer--{{ $answer->source->value }}">
            <p class="answer__question">{{ $answer->number }} {{ $answer->label }}</p>

            @if ($answer->isMissing())
                <p class="answer__value answer__value--missing">{{ __('ap_report.not_recorded') }}</p>
            @elseif ($answer->isMultiValued())
                <ul class="answer__value">
                    @foreach ($answer->values as $value)
                        <li>{{ $value }}</li>
                    @endforeach
                </ul>
            @else
                <p class="answer__value">{{ $answer->values[0] }}</p>
            @endif

            <p class="answer__source">
                @switch($answer->source)
                    @case(AnswerSource::RECORDED)
                        {{ __('ap_report.source_recorded') }}
                        @break
                    @case(AnswerSource::DERIVED)
                        {{ __('ap_report.source_derived') }}
                        @if ($answer->origins !== [])
                            &mdash; {{ __('ap_report.origin_prefix') }}: {{ implode(', ', $answer->origins) }}
                        @endif
                        @break
                    @default
                        {{ __('ap_report.source_missing') }}
                @endswitch
            </p>
        </div>
    @endforeach
@endforeach
