@php
    /** @var array<string, mixed> $details */
    $outsideEu = $details['outside_eu'] ?? null;
    $decisions = $details['automated_decision_making'] ?? null;
@endphp
# {{ __('snapshot.public_details') }}
{{ __('snapshot.public_details_help') }}

- **{{ __('avg_responsible_processing_record.public_summary') }}**: {!! Str::toSingleLineEscapedString($details['summary'] ?? null, '-') !!}
@foreach ($details['goals'] ?? [] as $goal)
- **{{ __('avg_goal.goal') }}**: {!! Str::toSingleLineEscapedString($goal['goal'] ?? null, '-') !!}
  - **{{ __('avg_goal.avg_goal_legal_base') }}**: {!! Str::toSingleLineEscapedString(App\Enums\AvgLegalBasis::tryFrom((string) ($goal['legal_basis'] ?? ''))?->label(), '-') !!}
@endforeach
@foreach ($details['stakeholders'] ?? [] as $stakeholder)
- **{{ __('stakeholder.model_singular') }}**: {!! Str::toSingleLineEscapedString($stakeholder['description'] ?? null, '-') !!}
  - **{{ __('stakeholder.citizen_service_numbers') }}**: {{ ($stakeholder['citizen_service_number'] ?? false) ? __('general.yes') : __('general.no') }}
  - **{{ __('stakeholder.special_collected_data') }}**: {!! Str::toSingleLineEscapedString(collect($stakeholder['special_categories'] ?? [])->map(static fn (string $category): string => __('stakeholder.' . $category))->implode('; '), '-') !!}
@endforeach
- **{{ __('avg_responsible_processing_record.has_processors') }}**: {{ ($details['has_processors'] ?? false) ? __('general.yes') : __('general.no') }}
- **{{ __('avg_responsible_processing_record.outside_eu') }}**: {{ is_array($outsideEu) && ($outsideEu['applies'] ?? false) ? __('general.yes') : __('general.no') }}
@if (is_array($outsideEu) && ($outsideEu['applies'] ?? false))
  - **{{ __('avg_responsible_processing_record.outside_eu_description') }}**: {!! Str::toSingleLineEscapedString($outsideEu['description'] ?? null, '-') !!}
  - **{{ __('avg_responsible_processing_record.outside_eu_protection_level') }}**: {{ ($outsideEu['adequacy_decision'] ?? false) ? __('general.yes') : __('general.no') }}
  - **{{ __('avg_responsible_processing_record.outside_eu_protection_level_description') }}**: {!! Str::toSingleLineEscapedString($outsideEu['safeguards'] ?? null, '-') !!}
@endif
- **{{ __('avg_responsible_processing_record.decision_making') }}**: {{ is_array($decisions) && ($decisions['applies'] ?? false) ? __('general.yes') : __('general.no') }}
@if (is_array($decisions) && ($decisions['applies'] ?? false))
  - **{{ __('avg_responsible_processing_record.logic') }}**: {!! Str::toSingleLineEscapedString($decisions['logic'] ?? null, '-') !!}
  - **{{ __('avg_responsible_processing_record.importance_consequences') }}**: {!! Str::toSingleLineEscapedString($decisions['consequences'] ?? null, '-') !!}
@endif
