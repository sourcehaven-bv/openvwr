@php use App\Models\Responsible; @endphp
@php
    /** @var App\Models\Avg\AvgProcessorProcessingRecord $record */
@endphp

## {{ __('avg_processor_processing_record.step_processing_name') }}

- **{{ __('processing_record.number') }}**: {!! Str::toSingleLineEscapedString($record->getNumber()) !!}
- **{{ __('processing_record.import_number') }}**: {!! Str::toSingleLineEscapedString($record->import_id) !!}
- **{{ __('processing_record.name') }}**: {!! Str::toSingleLineEscapedString($record->name) !!}
- **{{ __('general.data_collection_source') }}**: {{ __(sprintf('core_entity_level.%s', $record->data_collection_source->value)) }}
- **{{ __('avg_processor_processing_record_service.model_singular') }}**: {!! Str::toSingleLineEscapedString($record->avgProcessorProcessingRecordService->name) !!}
- **{{ __('general.review_at') }}**: {{ DateFormat::toDate($record->review_at) }}
- **{{ __('general.parent') }}**: {!! Str::toSingleLineEscapedString($record->parent?->getNumber(), '-') !!}

## {{ __('avg_processor_processing_record.step_responsible') }}

<!--- #App\Models\Responsible# --->

## {{ __('avg_processor_processing_record.step_processor') }}

<!--- #App\Models\Processor# --->

## {{ __('avg_processor_processing_record.step_receiver') }}

<!--- #App\Models\Receiver# --->

## {{ __('avg_processor_processing_record.step_processing_goal') }}

@forelse ($record->avgGoals as $goal)
- **{{ __('avg_goal.goal') }}**: {!! Str::toSingleLineEscapedString($goal->goal, '-') !!}
  - **{{ __('avg_goal_legal_base.model_plural') }}**: {!! Str::toSingleLineEscapedString($goal->avg_goal_legal_base) !!}
  - **{{ __('avg_goal.remarks') }}**: {!! Str::toSingleLineEscapedString($goal->remarks, '-') !!}
@empty
- Geen
@endforelse

## {{ __('avg_processor_processing_record.step_involved_data') }}

@forelse ($record->stakeholders as $stakeholder)
- **{{ __('general.description') }}**: {!! Str::toSingleLineEscapedString($stakeholder->description, '-') !!}
- **{{ __('stakeholder.special_collected_data') }}**
    - **{{ __('stakeholder.biometric') }}**: {{ $stakeholder->biometric ? 'ja' : 'nee'}}
    - **{{ __('stakeholder.faith_or_belief') }}**: {{ $stakeholder->faith_or_belief ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.genetic') }}**: {{ $stakeholder->genetic ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.health') }}**: {{ $stakeholder->health ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.political_attitude') }}**: {{ $stakeholder->political_attitude ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.race_or_ethnicity') }}**: {{ $stakeholder->race_or_ethnicity ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.sexual_life') }}**: {{ $stakeholder->sexual_life ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.trade_association_membership') }}**: {{ $stakeholder->trade_association_membership ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.special_collected_data_explanation') }}**: {!! Str::toSingleLineEscapedString($stakeholder->special_collected_data_explanation, '-') !!}- **{{ __('stakeholder.sensitive_data') }}**
    - **{{ __('stakeholder.criminal_law') }}**: {{ $stakeholder->criminal_law ? 'ja' : 'nee' }}
    - **{{ __('stakeholder.citizen_service_numbers') }}**: {{ $stakeholder->citizen_service_numbers ? 'ja' : 'nee' }}
  - **{{ __('stakeholder_data_item.model_plural') }}**
@foreach($stakeholder->stakeholderDataItems as $stakeholderDataItem)
    - **{{ __('general.description') }}**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->description, '-') !!}
      - **{{ __('stakeholder_data_item.collection_purpose') }}**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->collection_purpose, '-') !!}
      - **{{ __('stakeholder_data_item.retention_period') }}**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->retention_period, '-') !!}
      - **{{ __('stakeholder_data_item.source_description') }}**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->source_description, '-') !!}
      - **{{ __('stakeholder_data_item.is_stakeholder_mandatory') }}**: {{ $stakeholderDataItem->is_stakeholder_mandatory ? 'ja' : 'nee' }}
      - **{{ __('stakeholder_data_item.stakeholder_consequences') }}**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->stakeholder_consequences, '-') !!}
@endforeach
@empty
- Geen
@endforelse

## {{ __('avg_processor_processing_record.step_decision_making') }}

- **{{ __('avg_processor_processing_record.decision_making') }}**: {{ $record->decision_making ? 'ja' : 'nee' }}
- **{{ __('avg_processor_processing_record.logic') }}**: {!! Str::toSingleLineEscapedString($record->logic, '-') !!}
- **{{ __('avg_processor_processing_record.importance_consequences') }}**: {!! Str::toSingleLineEscapedString($record->importance_consequences, '-') !!}

## {{ __('avg_processor_processing_record.step_system') }}

<!--- #App\Models\System# --->

## {{ __('avg_processor_processing_record.step_security') }}

- **{{ __('avg_processor_processing_record.has_security') }}**: {{ $record->has_security ? 'ja' : 'nee' }}
- **{{ __('processor.measures') }}**
- **{{ __('processor.measures_implemented') }}**: {{ $record->measures_implemented ? 'ja' : 'nee' }}
- **{{ __('processor.other_measures') }}**: {{ $record->other_measures ? 'ja' : 'nee' }}
- **{{ __('processor.measures_description') }}**: {!! Str::toSingleLineEscapedString($record->measures_description, '-') !!}
- **{{ __('avg_processor_processing_record.has_pseudonymization') }}**: {{ $record->has_pseudonymization ? 'ja' : 'nee' }}
- **{{ __('avg_processor_processing_record.pseudonymization') }}**: {!! Str::toSingleLineEscapedString($record->pseudonymization, '-') !!}

## {{ __('avg_processor_processing_record.step_passthrough') }}

- **{{ __('avg_processor_processing_record.outside_eu') }}**: {{ $record->outside_eu ? 'ja' : 'nee' }}
- **{{ __('general.country') }}**: {!! Str::toSingleLineEscapedString($record->country, '-') !!} {!! Str::toSingleLineEscapedString($record->country_other) !!}
- **{{ __('avg_processor_processing_record.outside_eu_protection_level') }}**: {{ $record->outside_eu_protection_level ? 'ja' : 'nee' }}
- **{{ __('avg_processor_processing_record.outside_eu_protection_level_description') }}**: {!! Str::toSingleLineEscapedString($record->outside_eu_protection_level_description, '-') !!}
- **{{ __('avg_processor_processing_record.outside_eu_description') }}**: {!! Str::toSingleLineEscapedString($record->outside_eu_description, '-') !!}

## {{ __('avg_processor_processing_record.step_geb_pia') }}

- **{{ __('avg_processor_processing_record.geb_pia') }}**: {{ $record->geb_pia ? 'ja' : 'nee' }}

## {{ __('avg_processor_processing_record.step_contact_person') }}

@foreach($record->users as $user)
- {!! Str::toSingleLineEscapedString($user->name) !!} @if($user->email)&lt;{!! Str::toSingleLineEscapedString($user->email) !!}&gt;@endif

@endforeach

<!--- #App\Models\ContactPerson# --->

## {{ __('avg_processor_processing_record.step_attachments') }}

@forelse($record->documents as $document)
- **{{ __('document.model_singular') }}**: {!! Str::toSingleLineEscapedString($document->name) !!}
@empty
- Geen
@endforelse

## {{ __('avg_processor_processing_record.step_remarks') }}

@forelse($record->remarks as $remark)
- **{{ __('remark.model_singular') }}**: {!! Str::toSingleLineEscapedString($remark->body, '-') !!}
@empty
- Geen
@endforelse

## {{ __('avg_processor_processing_record.step_publish') }}

- **{{ __('general.public_from') }}**: {{ $record->public_from ? DateFormat::toDateTime($record->public_from) : '-' }}
