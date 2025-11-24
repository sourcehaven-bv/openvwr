@php
    /** @var App\Models\Avg\AvgResponsibleProcessingRecord $record */
@endphp

# {!! Str::toSingleLineEscapedString($record->name) !!}

@if($record->avgGoals->count() === 1)
Hier vindt u een beschrijving van gegevensverwerking _{!! trim(Str::toSingleLineEscapedString($record->name)) !!}_ in het kader van de Algemene Verordening Gegevensbescherming (AVG), met als doel:

{!! Str::toSingleLineEscapedString($record->avgGoals->first()->goal) !!}
@else
Hier vindt u een beschrijving van gegevensverwerking _{!! trim(Str::toSingleLineEscapedString($record->name)) !!}_ in het kader van de Algemene Verordening Gegevensbescherming (AVG), met als doelen:

@foreach ($record->avgGoals as $goal)
- {!! Str::toSingleLineEscapedString($goal->goal) !!}
@endforeach
@endif

@if($record->stakeholders->count() > 0)
# Betrokkenen en gegevens
Van wie worden welke gegevens verwerkt en wat is het doel hiervan.

@foreach ($record->stakeholders as $stakeholder)
@if($stakeholder->stakeholderDataItems->count() > 0)
#### {!! trim(Str::toSingleLineEscapedString($stakeholder->description)) !!}
@foreach ($stakeholder->stakeholderDataItems as $stakeholderDataItem)
- **Beschrijving**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->description) !!}
  - **Verzameldoel**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->collection_purpose) !!}
  - **Bewaartermijn**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->retention_period) !!}
  - **Bron**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->source_description) !!}
@if($stakeholderDataItem->is_stakeholder_mandatory === true)
  - **Betrokkenen** waren <u>wel</u> verplicht deze gegevens aan te leveren.
  - **Consequenties**: {!! Str::toSingleLineEscapedString($stakeholderDataItem->stakeholder_consequences) !!}
@else
  - **Betrokkenen** waren <u>niet</u> verplicht deze gegevens aan te leveren.
@endif

@endforeach
@endif
@endforeach
@endif

# Automatische besluitvorming
Er is <u>{{ $record->decision_making ? 'wel' : 'geen'}}</u> sprake van besluitvorming over persoonsgegevens op basis van automatisch verwerkte gegevens.

<!--- #App\Models\Receiver# --->

# Doorgifte buiten EER
Er is <u>{{ $record->outside_eu ? 'wel' : 'geen'}}</u> sprake van doorgifte van persoonsgegevens aan één of meer landen buiten de Europese Economische Ruimte (EER) of aan een internationale organisatie.

<!--- #App\Models\Responsible# --->
