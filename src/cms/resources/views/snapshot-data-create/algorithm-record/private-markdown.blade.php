@php
    /** @var App\Models\Algorithm\AlgorithmRecord $record */
@endphp

# {!! Str::toSingleLineEscapedString($record->name) !!}

## {{ __('algorithm_record.step_processing_name') }}

- **{{ __('algorithm_record.number') }}**: {!! Str::toSingleLineEscapedString($record->getNumber()) !!}
- **{{ __('algorithm_record.description') }}**: {!! Str::toSingleLineEscapedString($record->description, '-') !!}
- **{{ __('algorithm_record.theme') }}**: {!! Str::toSingleLineEscapedString($record->algorithmTheme?->name, '-') !!}
- **{{ __('algorithm_record.status') }}**: {!! Str::toSingleLineEscapedString($record->algorithmStatus?->name, '-') !!}
- **{{ __('algorithm_record.start_date') }}**: {{ DateFormat::toDate($record->start_date) ?? '-' }}
- **{{ __('algorithm_record.end_date') }}**: {{ DateFormat::toDate($record->end_date) ?? '-' }}
- **{{ __('algorithm_record.contact_data') }}**: {!! Str::toSingleLineEscapedString($record->contact_data, '-') !!}
- **{{ __('algorithm_record.public_page_link') }}**: {!! Str::toSingleLineEscapedString($record->public_page_link, '-') !!}
- **{{ __('algorithm_record.publication_category') }}**: {!! Str::toSingleLineEscapedString($record->algorithmPublicationCategory?->name, '-') !!}
- **{{ __('algorithm_record.source_link') }}**: {!! Str::toSingleLineEscapedString($record->source_link, '-') !!}

## {{ __('algorithm_record.step_responsible_use') }}

- **{{ __('algorithm_record.resp_goal_and_impact') }}**: {!! Str::toSingleLineEscapedString($record->resp_goal_and_impact, '-') !!}
- **{{ __('algorithm_record.resp_considerations') }}**: {!! Str::toSingleLineEscapedString($record->resp_considerations, '-') !!}
- **{{ __('algorithm_record.resp_human_intervention') }}**: {!! Str::toSingleLineEscapedString($record->resp_human_intervention, '-') !!}
- **{{ __('algorithm_record.resp_risk_analysis') }}**: {!! Str::toSingleLineEscapedString($record->resp_risk_analysis, '-') !!}
- **{{ __('algorithm_record.resp_legal_base_title') }}**: {!! Str::toSingleLineEscapedString($record->resp_legal_base_title, '-') !!}
- **{{ __('algorithm_record.resp_legal_base') }}**: {!! Str::toSingleLineEscapedString($record->resp_legal_base, '-') !!}
- **{{ __('algorithm_record.resp_legal_base_link') }}**: {!! Str::toSingleLineEscapedString($record->resp_legal_base_link, '-') !!}
- **{{ __('algorithm_record.resp_processor_registry_link') }}**: {!! Str::toSingleLineEscapedString($record->resp_processor_registry_link, '-') !!}
- **{{ __('algorithm_record.resp_impact_tests') }}**: {!! Str::toSingleLineEscapedString($record->resp_impact_tests, '-') !!}
- **{{ __('algorithm_record.resp_impact_test_links') }}**: {!! Str::toSingleLineEscapedString($record->resp_impact_test_links, '-') !!}
- **{{ __('algorithm_record.resp_impact_tests_description') }}**: {!! Str::toSingleLineEscapedString($record->resp_impact_tests_description, '-') !!}

## {{ __('algorithm_record.step_mechanics') }}

- **{{ __('algorithm_record.oper_data_title') }}**: {!! Str::toSingleLineEscapedString($record->oper_data_title, '-') !!}
- **{{ __('algorithm_record.oper_data') }}**: {!! Str::toSingleLineEscapedString($record->oper_data, '-') !!}
- **{{ __('algorithm_record.oper_links') }}**: {!! Str::toSingleLineEscapedString($record->oper_links, '-') !!}
- **{{ __('algorithm_record.oper_technical_operation') }}**: {!! Str::toSingleLineEscapedString($record->oper_technical_operation, '-') !!}
- **{{ __('algorithm_record.oper_supplier') }}**: {!! Str::toSingleLineEscapedString($record->oper_supplier, '-') !!}
- **{{ __('algorithm_record.oper_source_code_link') }}**: {!! Str::toSingleLineEscapedString($record->oper_source_code_link, '-') !!}

## {{ __('algorithm_record.step_meta') }}

- **{{ __('algorithm_record.meta_date_of_development') }}**: {{ DateFormat::toDate($record->meta_date_of_development) ?? '-' }}
- **{{ __('algorithm_record.meta_owner_algorithm') }}**: {!! Str::toSingleLineEscapedString($record->meta_owner_algorithm, '-') !!}
- **{{ __('algorithm_record.meta_product_owner_algorithm') }}**: {!! Str::toSingleLineEscapedString($record->meta_product_owner_algorithm, '-') !!}
- **{{ __('algorithm_record.meta_national_id') }}**: {!! Str::toSingleLineEscapedString($record->meta_national_id, '-') !!}
- **{{ __('algorithm_record.meta_source_id') }}**: {!! Str::toSingleLineEscapedString($record->meta_source_id, '-') !!}
- **{{ __('algorithm_record.meta_tags') }}**: {!! Str::toSingleLineEscapedString($record->meta_tags, '-') !!}

## {{ __('algorithm_record.step_impact') }}

- **{{ __('algorithm_record.impact_with_consequences') }}**: {{ $record->impact_with_consequences === null ? '-' : ($record->impact_with_consequences ? __('general.yes') : __('general.no')) }}
- **{{ __('algorithm_record.impact_more_algorithms_applied') }}**: {{ $record->impact_more_algorithms_applied === null ? '-' : ($record->impact_more_algorithms_applied ? __('general.yes') : __('general.no')) }}
- **{{ __('algorithm_record.impact_effect_on_outcome') }}**: {{ $record->impact_effect_on_outcome === null ? '-' : ($record->impact_effect_on_outcome ? __('general.yes') : __('general.no')) }}

## {{ __('algorithm_record.step_validation') }}

- **{{ __('algorithm_record.validation_answers_checked_by_product_owner') }}**: {{ $record->validation_answers_checked_by_product_owner === null ? '-' : ($record->validation_answers_checked_by_product_owner ? __('general.yes') : __('general.no')) }}
