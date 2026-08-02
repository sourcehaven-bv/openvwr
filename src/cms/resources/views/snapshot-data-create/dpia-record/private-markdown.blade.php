@php
    /** @var App\Models\Dpia\DpiaRecord $record */
@endphp

# {!! Str::toSingleLineEscapedString($record->name) !!}

- **{{ __('general.number') }}**: {!! Str::toSingleLineEscapedString($record->getNumber()) !!}
- **{{ __('dpia_record.subject_type') }}**: {!! Str::toSingleLineEscapedString($record->subject_type?->label(), '-') !!}
- **{{ __('dpia_record.prescan') }}**: {!! Str::toSingleLineEscapedString($record->dpiaPrescanRecord?->name, '-') !!}
- **{{ __('dpia_record.assessed_at') }}**: {{ DateFormat::toDate($record->assessed_at) ?? '-' }}
- **{{ __('dpia_record.review_at') }}**: {{ DateFormat::toDate($record->getReviewAt()) ?? '-' }}

## {{ __('dpia_record.step_proposal') }}

- **{{ __('dpia_record.proposal_description') }}**: {!! Str::toSingleLineEscapedString($record->proposal_description, '-') !!}
- **{{ __('dpia_record.proposal_motivation') }}**: {!! Str::toSingleLineEscapedString($record->proposal_motivation, '-') !!}

## {{ __('dpia_record.step_personal_data') }}

@forelse ($record->personalData as $personalData)
- **{!! Str::toSingleLineEscapedString($personalData->description, '-') !!}**
    - {{ __('dpia_record.personal_data_type') }}: {!! Str::toSingleLineEscapedString($personalData->type?->label(), '-') !!}
    - {{ __('dpia_record.personal_data_subject_category') }}: {!! Str::toSingleLineEscapedString($personalData->data_subject_category, '-') !!}
    - {{ __('dpia_record.personal_data_source') }}: {!! Str::toSingleLineEscapedString($personalData->source, '-') !!}
    - {{ __('dpia_record.personal_data_retention_period') }}: {!! Str::toSingleLineEscapedString($personalData->retention_period, '-') !!}
    - {{ __('dpia_record.personal_data_exception_ground') }}: {!! Str::toSingleLineEscapedString($personalData->exception_ground, '-') !!}
@empty
- -
@endforelse

- **{{ __('dpia_record.personal_data_sources') }}**: {!! Str::toSingleLineEscapedString($record->personal_data_sources, '-') !!}

## {{ __('dpia_record.step_processing') }}

- **{{ __('dpia_record.processing_description') }}**: {!! Str::toSingleLineEscapedString($record->processing_description, '-') !!}

## {{ __('dpia_record.step_techniques') }}

- **{{ __('dpia_record.techniques_description') }}**: {!! Str::toSingleLineEscapedString($record->techniques_description, '-') !!}
- **{{ __('dpia_record.automated_decision_making') }}**: {{ $record->automated_decision_making ? 'ja' : 'nee' }}
- **{{ __('dpia_record.profiling') }}**: {{ $record->profiling ? 'ja' : 'nee' }}
- **{{ __('dpia_record.cloud_processing') }}**: {{ $record->cloud_processing ? 'ja' : 'nee' }}
- **{{ __('dpia_record.big_data_processing') }}**: {{ $record->big_data_processing ? 'ja' : 'nee' }}
- **{{ __('dpia_record.techniques_explanation') }}**: {!! Str::toSingleLineEscapedString($record->techniques_explanation, '-') !!}

## {{ __('dpia_record.step_purposes') }}

- **{{ __('dpia_record.purpose_description') }}**: {!! Str::toSingleLineEscapedString($record->purpose_description, '-') !!}

## {{ __('dpia_record.step_parties') }}

- **{{ __('dpia_record.parties_description') }}**: {!! Str::toSingleLineEscapedString($record->parties_description, '-') !!}
- **{{ __('dpia_record.parties_access') }}**: {!! Str::toSingleLineEscapedString($record->parties_access, '-') !!}

## {{ __('dpia_record.step_interests') }}

- **{{ __('dpia_record.interests_description') }}**: {!! Str::toSingleLineEscapedString($record->interests_description, '-') !!}
- **{{ __('dpia_record.interests_data_subjects') }}**: {!! Str::toSingleLineEscapedString($record->interests_data_subjects, '-') !!}

## {{ __('dpia_record.step_locations') }}

- **{{ __('dpia_record.processing_locations') }}**: {!! Str::toSingleLineEscapedString($record->processing_locations, '-') !!}
- **{{ __('dpia_record.outside_eea') }}**: {{ $record->outside_eea ? 'ja' : 'nee' }}
- **{{ __('dpia_record.transfer_mechanism') }}**: {!! Str::toSingleLineEscapedString($record->transfer_mechanism, '-') !!}
- **{{ __('dpia_record.transfer_safeguards') }}**: {!! Str::toSingleLineEscapedString($record->transfer_safeguards, '-') !!}

## {{ __('dpia_record.step_legal_framework') }}

- **{{ __('dpia_record.legal_policy_framework') }}**: {!! Str::toSingleLineEscapedString($record->legal_policy_framework, '-') !!}

## {{ __('dpia_record.step_retention') }}

- **{{ __('dpia_record.retention_periods') }}**: {!! Str::toSingleLineEscapedString($record->retention_periods, '-') !!}
- **{{ __('dpia_record.retention_motivation') }}**: {!! Str::toSingleLineEscapedString($record->retention_motivation, '-') !!}
- **{{ __('dpia_record.retention_responsible') }}**: {!! Str::toSingleLineEscapedString($record->retention_responsible, '-') !!}

## {{ __('dpia_record.step_legal_basis') }}

- **{{ __('dpia_record.legal_basis') }}**: {!! Str::toSingleLineEscapedString($record->legal_basis, '-') !!}
- **{{ __('dpia_record.legal_basis_conditions') }}**: {!! Str::toSingleLineEscapedString($record->legal_basis_conditions, '-') !!}

## {{ __('dpia_record.step_special_categories') }}

- **{{ __('dpia_record.special_categories_additional') }}**: {!! Str::toSingleLineEscapedString($record->special_categories_exception, '-') !!}

## {{ __('dpia_record.step_purpose_limitation') }}

- **{{ __('dpia_record.further_processing') }}**: {{ $record->further_processing ? 'ja' : 'nee' }}
- **{{ __('dpia_record.purpose_limitation') }}**: {!! Str::toSingleLineEscapedString($record->purpose_limitation, '-') !!}

## {{ __('dpia_record.step_necessity') }}

- **{{ __('dpia_record.necessity_proportionality') }}**: {!! Str::toSingleLineEscapedString($record->necessity_proportionality, '-') !!}
- **{{ __('dpia_record.necessity_subsidiarity') }}**: {!! Str::toSingleLineEscapedString($record->necessity_subsidiarity, '-') !!}

## {{ __('dpia_record.step_rights') }}

- **{{ __('dpia_record.data_subject_rights_procedure') }}**: {!! Str::toSingleLineEscapedString($record->data_subject_rights_procedure, '-') !!}
- **{{ __('dpia_record.rights_restricted') }}**: {{ $record->rights_restricted ? 'ja' : 'nee' }}
- **{{ __('dpia_record.rights_restriction_basis') }}**: {!! Str::toSingleLineEscapedString($record->rights_restriction_basis, '-') !!}

## {{ __('dpia_record.step_risks') }}

@forelse ($record->risks as $risk)
- **{!! Str::toSingleLineEscapedString($risk->title, '-') !!}**
    - {{ __('dpia_record.risk_description') }}: {!! Str::toSingleLineEscapedString($risk->description, '-') !!}
    - {{ __('dpia_record.risk_origin') }}: {!! Str::toSingleLineEscapedString($risk->origin, '-') !!}
    - {{ __('dpia_record.risk_likelihood') }}: {!! Str::toSingleLineEscapedString($risk->likelihood?->label(), '-') !!}
    - {{ __('dpia_record.risk_likelihood_motivation') }}: {!! Str::toSingleLineEscapedString($risk->likelihood_motivation, '-') !!}
    - {{ __('dpia_record.risk_impact') }}: {!! Str::toSingleLineEscapedString($risk->impact?->label(), '-') !!}
    - {{ __('dpia_record.risk_impact_motivation') }}: {!! Str::toSingleLineEscapedString($risk->impact_motivation, '-') !!}
    - {{ __('dpia_record.risk_level') }}: {!! Str::toSingleLineEscapedString($risk->level?->label(), '-') !!}
    - {{ __('dpia_record.risk_level_motivation') }}: {!! Str::toSingleLineEscapedString($risk->level_motivation, '-') !!}
@empty
- -
@endforelse

- **{{ __('dpia_record.risks_additional_information') }}**: {!! Str::toSingleLineEscapedString($record->risks_additional_information, '-') !!}

## {{ __('dpia_record.step_measures') }}

@forelse ($record->measures as $measure)
- **{!! Str::toSingleLineEscapedString($measure->description, '-') !!}**
    - {{ __('dpia_record.measure_type') }}: {!! Str::toSingleLineEscapedString($measure->type?->label(), '-') !!}
    - {{ __('dpia_record.measure_risks') }}: {!! Str::toSingleLineEscapedString($measure->risks->map(fn ($risk) => $risk->label())->implode('; '), '-') !!}
    - {{ __('dpia_record.measure_origin') }}: {!! Str::toSingleLineEscapedString($measure->origin, '-') !!}
    - {{ __('dpia_record.measure_residual_level') }}: {!! Str::toSingleLineEscapedString($measure->residual_level?->label(), '-') !!}
    - {{ __('dpia_record.measure_ap_advice') }}: {!! Str::toSingleLineEscapedString($measure->ap_advice, '-') !!}
    - {{ __('dpia_record.measure_monitoring_country') }}: {!! Str::toSingleLineEscapedString($measure->monitoring_country, '-') !!}
    - {{ __('dpia_record.measure_owner') }}: {!! Str::toSingleLineEscapedString($measure->owner, '-') !!}
@empty
- -
@endforelse

- **{{ __('dpia_record.measures_additional_information') }}**: {!! Str::toSingleLineEscapedString($record->measures_additional_information, '-') !!}
- **{{ __('dpia_record.residual_risk_acceptance') }}**: {!! Str::toSingleLineEscapedString($record->residual_risk_acceptance, '-') !!}

## {{ __('dpia_record.step_consultation') }}

- **{{ __('dpia_record.data_subjects_consulted') }}**: {{ $record->data_subjects_consulted ? 'ja' : 'nee' }}
- **{{ __('dpia_record.data_subjects_consultation') }}**: {!! Str::toSingleLineEscapedString($record->data_subjects_consultation, '-') !!}
- **{{ __('dpia_record.fg_advice') }}**: {!! Str::toSingleLineEscapedString($record->fg_advice, '-') !!}
- **{{ __('dpia_record.fg_advice_followup') }}**: {!! Str::toSingleLineEscapedString($record->fg_advice_followup, '-') !!}
- **{{ __('dpia_record.fg_advice_received_at') }}**: {{ DateFormat::toDate($record->fg_advice_received_at) ?? '-' }}
- **{{ __('dpia_record.ap_consultation_required') }}**: {{ $record->ap_consultation_required ? 'ja' : 'nee' }}
- **{{ __('dpia_record.ap_consultation') }}**: {!! Str::toSingleLineEscapedString($record->ap_consultation, '-') !!}
- **{{ __('dpia_record.ap_consultation_requested_at') }}**: {{ DateFormat::toDate($record->ap_consultation_requested_at) ?? '-' }}

## {{ __('dpia_record.step_review') }}

- **{{ __('dpia_record.management_summary') }}**: {!! Str::toSingleLineEscapedString($record->management_summary, '-') !!}

## {{ __('dpia_record.step_relations') }}

@forelse ($record->avgResponsibleProcessingRecords as $processingRecord)
- {!! Str::toSingleLineEscapedString($processingRecord->name) !!}
@empty
- -
@endforelse
