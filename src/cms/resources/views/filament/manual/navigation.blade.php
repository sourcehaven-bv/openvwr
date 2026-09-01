{{--
    The manual's own menu, which replaces the panel navigation entirely.

    Both layers are in one list because that is how the manual is read: you
    start at a task and drop into the naslag for the explanation, or you look a
    subject up directly. Everything here comes from App\Manual, which has
    already dropped what a feature flag hides, so the menu can never offer a
    page that would 404.
--}}
@php
    use App\Filament\Pages\Manual\ManualUrls;

    $manual = $livewire->manual();
    $current = request()->url();
@endphp

<nav class="manual-nav">
    <a
        href="{{ ManualUrls::home() }}"
        @class(['manual-nav__home', 'manual-nav__home--active' => $current === ManualUrls::home()])
    >
        <x-filament::icon icon="heroicon-m-home" class="manual-nav__home-icon" />
        {{ __('manual.tasks_heading') }}
    </a>

    <p class="manual-nav__section">{{ __('manual.tasks') }}</p>

    @foreach ($manual->taskGroups() as $group)
        <div class="manual-nav__group">
            <p class="manual-nav__group-title">{{ $group['title'] }}</p>

            @foreach ($group['tasks'] as $task)
                @php $url = ManualUrls::task($task); @endphp
                <a
                    href="{{ $url }}"
                    @class(['manual-nav__item', 'manual-nav__item--active' => $current === $url])
                >
                    {{ $task->title }}
                </a>
            @endforeach
        </div>
    @endforeach

    <p class="manual-nav__section">{{ __('manual.reference') }}</p>

    @foreach ($manual->chapters() as $chapter)
        <div class="manual-nav__group">
            <p class="manual-nav__group-title">{{ $chapter->title }}</p>

            @foreach ($chapter->topics as $topic)
                @php $url = ManualUrls::topic($topic); @endphp
                <a
                    href="{{ $url }}"
                    @class(['manual-nav__item', 'manual-nav__item--active' => $current === $url])
                >
                    {{ $topic->title }}
                </a>
            @endforeach
        </div>
    @endforeach
</nav>
