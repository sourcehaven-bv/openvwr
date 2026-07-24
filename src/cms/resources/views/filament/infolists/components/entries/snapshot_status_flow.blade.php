@php
    use App\Facades\DateFormat;

    /** @var array{stations: array<int, array<string, mixed>>, obsolete: array<string, mixed>|null} $flow */
    $flow = $getState();

    // Render every station left-to-right, including the Obsolete branch when
    // present, so the connector logic stays a single loop.
    $stations = $flow['stations'];
    if ($flow['obsolete'] !== null) {
        $stations[] = $flow['obsolete'];
    }

    // Only the active (current) station is coloured; reached-but-past stations
    // are neutral, and not-yet-reached stations are faint. Colours come from
    // Filament's CSS custom properties (bg-{color}-500 utilities are not all
    // generated, so info/success would otherwise render transparent).
    $currentColor = static function (array $s): string {
        return "rgb(var(--{$s['color']}-500))";
    };
@endphp

<div class="fi-snapshot-status-flow w-full overflow-x-auto py-2">
    <ol class="flex min-w-full" role="list">
        @foreach ($stations as $index => $station)
            @php
                $reached = $station['reached'];
                $current = $station['current'];
                $skipped = $station['skipped'] ?? false;
            @endphp

            {{-- Connector from the previous station, aligned to the dot centre
                 (dot is h-8 = 2rem, so its centre is 1rem from the top). A dashed
                 connector leads into a skipped station to signal the jump. --}}
            @if ($index > 0)
                <li aria-hidden="true" class="flex-1 min-w-6 px-1" style="padding-top: calc(1rem - 2px);">
                    <span
                        @class([
                            'block h-1 w-full',
                            'rounded-full bg-gray-300 dark:bg-gray-600' => $reached,
                            'rounded-full bg-gray-200 dark:bg-white/10' => ! $reached && ! $skipped,
                            // Skipped: dashed line instead of a solid bar.
                            'border-t-2 border-dashed border-gray-300 dark:border-gray-600' => $skipped,
                        ])
                    ></span>
                </li>
            @endif

            {{-- Station --}}
            <li class="flex flex-col items-center shrink-0 w-28 text-center">
                <span
                    @class([
                        'flex h-8 w-8 items-center justify-center rounded-full',
                        // Past (reached, not current): neutral filled.
                        'bg-gray-300 dark:bg-gray-600' => $reached && ! $current,
                        // Skipped: faint dashed outline, no fill.
                        'border-2 border-dashed border-gray-300 dark:border-gray-600' => $skipped,
                        // Not reached yet: faint outline.
                        'bg-gray-200 dark:bg-white/10' => ! $reached && ! $skipped,
                        // Current: coloured + ring.
                        'ring-4 ring-offset-2 ring-offset-white dark:ring-offset-gray-900' => $current,
                    ])
                    @if ($current)
                        style="background: {{ $currentColor($station) }}; --tw-ring-color: {{ $currentColor($station) }};"
                    @endif
                >
                    @if ($reached)
                        <x-filament::icon
                            :icon="$current ? 'heroicon-m-map-pin' : ($station['color'] === 'gray' ? 'heroicon-m-x-mark' : 'heroicon-m-check')"
                            @class([
                                'h-5 w-5',
                                'text-white' => $current,
                                'text-gray-600 dark:text-gray-200' => ! $current,
                            ])
                        />
                    @endif
                </span>

                <span
                    @class([
                        'mt-2 text-sm',
                        'font-semibold' => $current,
                        'font-medium text-gray-700 dark:text-gray-300' => $reached && ! $current,
                        'font-medium text-gray-400 dark:text-gray-500' => ! $reached,
                    ])
                    @if ($current) style="color: {{ $currentColor($station) }};" @endif
                >
                    {{ $station['label'] }}
                </span>

                @if ($reached && $station['reached_at'])
                    <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ DateFormat::toDateTime($station['reached_at']) }}
                    </span>
                    @if ($station['reached_by'])
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $station['reached_by'] }}
                        </span>
                    @endif
                @elseif ($skipped)
                    <span class="mt-0.5 text-xs italic text-gray-400 dark:text-gray-500">
                        {{ __('snapshot.status_flow_skipped') }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</div>
