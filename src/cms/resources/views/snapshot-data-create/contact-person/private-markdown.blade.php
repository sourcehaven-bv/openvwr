@php
    /** @var App\Models\ContactPerson $record */
@endphp

{!! Str::toSingleLineEscapedString($record->name) !!} @if($record->email)&lt;{!! Str::toSingleLineEscapedString($record->email) !!}&gt;@endif
