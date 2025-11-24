@php
    use App\Models\States\Snapshot\Obsolete;
    $snapshots = $getRecord()->snapshots()
        ->whereNot('state', Obsolete::$name)
        ->orderBy('created_at', 'desc')
        ->get();
@endphp
    @foreach($snapshots as $snapshot)
        <x-filament::badge color="{{ $snapshot->state::$color }}" tooltip="{{ DateFormat::toDate($snapshot->created_at) }}">
            {{ __(sprintf('snapshot_state.label_short.%s', $snapshot->state->getValue())) }} (#{{ $snapshot->version }})
        </x-filament::badge>
        &nbsp;
    @endforeach
