@php
    /** @var App\Models\System $record */
@endphp

{!! Str::toSingleLineEscapedString($record->description, '-') !!}
