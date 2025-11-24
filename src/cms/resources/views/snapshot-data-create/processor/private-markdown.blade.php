@php
    /** @var App\Models\Processor $record */
@endphp

{!! Str::toSingleLineEscapedString($record->name) !!} ({!! Str::toSingleLineEscapedString($record->email) !!})
