@php
    /** @var App\Models\Snapshot $snapshot */
@endphp
<ul>
@if($snapshots->count() > 0)
@foreach ($snapshots as $snapshot)
  <li><div class="related-record">{!! $snapshot->snapshotData->private_markdown?->toHtml() !!}</div></li>
@endforeach
@else
  <li>geen</li>
@endif
</ul>
