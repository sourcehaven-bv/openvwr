@php
    use App\Filament\Pages\Manual\ManualUrls;
    use App\Manual\TaskCapability;

    $task = $this->task();
    $capability = $this->capability();
@endphp

<article class="manual-page manual-detail">
    <nav class="manual-crumbs">
        <a href="{{ ManualUrls::home() }}" class="manual-crumbs__link">{{ __('manual.tasks_heading') }}</a>
        <span class="manual-crumbs__sep">/</span>
        <span>{{ $task->group }}</span>
    </nav>

    <h1 class="manual-detail__title">{{ $task->title }}</h1>
    <p class="manual-detail__lede">{{ $task->summary }}</p>

    <div @class([
        'manual-rolegate',
        'manual-rolegate--ok' => $capability === TaskCapability::PERFORM,
        'manual-rolegate--blocked' => $capability === TaskCapability::NONE,
    ])>
        @php $roleName = $this->decidingRoleName(); @endphp

        @if ($capability === TaskCapability::PERFORM)
            {{ __('manual.role_can_perform', ['role' => $roleName]) }}
        @elseif ($capability === TaskCapability::READ)
            {{ __('manual.role_can_read', ['role' => $roleName]) }}
        @else
            {{ __('manual.role_cannot') }}
            @php $roles = $this->manual()->topic('rollen'); @endphp
            @if ($roles !== null)
                <a href="{{ ManualUrls::topic($roles) }}" class="manual-inline-link">
                    {{ __('manual.role_see_roles') }}
                </a>
            @endif
        @endif
    </div>

    <p class="manual-detail__intro">{{ $task->intro }}</p>

    <ol class="manual-steps">
        @foreach ($task->steps as $step)
            <li class="manual-steps__item">
                <h2 class="manual-steps__title">{{ $step->title }}</h2>
                <p class="manual-steps__body">{{ $step->body }}</p>

                @php $topics = $this->topicsOf($step->topicIds); @endphp
                @if ($topics !== [])
                    <div class="manual-seealso">
                        <span class="manual-seealso__label">{{ __('manual.see_reference') }}</span>
                        <span class="manual-seealso__links">
                            @foreach ($topics as $topic)
                                <a href="{{ ManualUrls::topic($topic) }}" class="manual-reflink">
                                    {{ $topic->title }}
                                </a>
                            @endforeach
                        </span>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>

    @if ($task->done !== null)
        <div class="manual-finish">
            <h2 class="manual-finish__title">{{ __('manual.done') }}</h2>
            <p class="manual-finish__body">{{ $task->done }}</p>
        </div>
    @endif
</article>
