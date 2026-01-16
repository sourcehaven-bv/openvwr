@php
    /** @var App\Models\Responsible $record */
@endphp
- **{{ __('responsible.name') }}**: {!! Str::toSingleLineEscapedString($record->name) !!}
@isset($record->address)
@isset($record->address->address)
  - **{{ __('address.section_address') }}**: {!! Str::toSingleLineEscapedString($record->address->address) !!}<br>{!! Str::toSingleLineEscapedString($record->address->postal_code) !!} {!! Str::toSingleLineEscapedString($record->address->city) !!}<br>{!! Str::toSingleLineEscapedString($record->address->country) !!}
@endisset
@isset($record->address->postbox)
  - **{{ __('address.section_postbox') }}**: {!! Str::toSingleLineEscapedString($record->address->postbox) !!}<br>{!! Str::toSingleLineEscapedString($record->address->postbox_postal_code) !!} {!! Str::toSingleLineEscapedString($record->address->postbox_city) !!}<br>{!! Str::toSingleLineEscapedString($record->address->postbox_country) !!}
@endisset
@endisset
