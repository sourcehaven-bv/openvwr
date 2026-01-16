@php
    /** @var App\Models\Responsible $record */
@endphp

# Verantwoordelijke

{!! Str::toSingleLineEscapedString($record->name) !!}
@isset($record->address)
@isset($record->address->address)

**Bezoekadres**<br>
{!! Str::toSingleLineEscapedString($record->address->address) !!}<br>
{!! Str::toSingleLineEscapedString($record->address->postal_code) !!} {!! Str::toSingleLineEscapedString($record->address->city) !!}<br>
{!! Str::toSingleLineEscapedString($record->address->country) !!}<br>
@endisset
@isset($record->address->postbox)

**Postadres**<br>
Postbus {!! Str::toSingleLineEscapedString($record->address->postbox) !!}<br>
{!! Str::toSingleLineEscapedString($record->address->postbox_postal_code) !!} {!! Str::toSingleLineEscapedString($record->address->postbox_city) !!}<br>
{!! Str::toSingleLineEscapedString($record->address->postbox_country) !!}<br>
@endisset
@endisset
