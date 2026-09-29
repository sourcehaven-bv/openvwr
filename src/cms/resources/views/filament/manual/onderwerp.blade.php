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
        {!! $this->body() !!}
    </div>

    {{--
        The backlinks. They are computed from the task definitions rather than
        written down here, so a topic can never claim to belong to work that
        does not in fact refer to it.

        A topic without any backlink is reference material you look up while
        doing something else, which is the normal case for a chapter like
        "Welkom". Saying so in an otherwise empty block adds nothing, so the
        whole section stays out rather than announcing its own emptiness.
    --}}
    @if ($usedIn !== [])
        <section class="manual-usedin">
            <h2 class="manual-usedin__title">{{ __('manual.used_in_tasks') }}</h2>

            <ul class="manual-usedin__list">
                @foreach ($usedIn as $task)
                    <li>
                        <a href="{{ ManualUrls::task($task) }}" class="manual-backlink">
                            {{ $task->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</article>
