@php
    use App\Filament\Pages\Manual\ManualUrls;

    $topic = $this->topic();
    $chapter = $this->chapter();
    $usedIn = $this->usedIn();
@endphp

<article class="manual-page manual-detail">
    <nav class="manual-crumbs">
        <a href="{{ ManualUrls::home() }}" class="manual-crumbs__link">{{ __('manual.tasks_heading') }}</a>
        <span class="manual-crumbs__sep">/</span>
        <span>{{ __('manual.reference') }}</span>
        @if ($chapter !== null)
            <span class="manual-crumbs__sep">/</span>
            <span>{{ $chapter->title }}</span>
        @endif
    </nav>

    <h1 class="manual-detail__title">{{ $topic->title }}</h1>

    @if ($topic->availability !== null)
        <p class="manual-availability">
            <span class="manual-availability__label">{{ __('manual.available_for') }}</span>
            {{ $topic->availability }}
        </p>
    @endif

    <div class="manual-prose">
        {!! $topic->html() !!}
    </div>

    {{--
        The backlinks. They are computed from the task definitions rather than
        written down here, so a topic can never claim to belong to work that
        does not in fact refer to it.
    --}}
    <section class="manual-usedin">
        <h2 class="manual-usedin__title">{{ __('manual.used_in_tasks') }}</h2>

        @if ($usedIn === [])
            <p class="manual-usedin__empty">{{ __('manual.used_in_no_tasks') }}</p>
        @else
            <ul class="manual-usedin__list">
                @foreach ($usedIn as $task)
                    <li>
                        <a href="{{ ManualUrls::task($task) }}" class="manual-backlink">
                            {{ $task->title }}
                            <span class="manual-backlink__steps">
                                {{ __('manual.step_count', ['count' => count($task->steps)]) }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</article>
