@php
    /** @var App\Models\Wpg\WpgProcessingRecord $record */
@endphp

# {!! Str::toSingleLineEscapedString($record->name) !!}

@foreach ($record->wpgGoals as $goal)
- {!! Str::toSingleLineEscapedString($goal->description) !!}
@endforeach
