@php
    /** @var App\Models\Wpg\WpgProcessingRecord $record */
@endphp

# {!! Str::toSingleLineEscapedString($record->name) !!}

@foreach ($record->wpgGoals as $goal)
- {!! Str::toSingleLineEscapedString($goal->description) !!}
@endforeach

## {{ __('wpg_processing_record.step_system_application') }}

- **{{ __('wpg_processing_record.has_algorithms') }}**: {{ $record->has_algorithms ? 'ja' : 'nee' }}

<!--- #App\Models\Algorithm\AlgorithmRecord# --->

