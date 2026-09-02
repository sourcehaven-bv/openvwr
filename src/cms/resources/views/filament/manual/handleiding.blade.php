@php
    use App\Filament\Pages\Manual\ManualUrls;
    use App\Manual\TaskCapability;

    $manual = $this->manual();
@endphp

<div class="manual-page">
    <div class="manual-search">
        <x-filament::input.wrapper :prefix-icon="'heroicon-m-magnifying-glass'">
            <x-filament::input
                type="search"
                wire:model.live.debounce.250ms="search"
                :placeholder="__('manual.search_placeholder')"
            />
        </x-filament::input.wrapper>

        @if ($this->isSearching())
            @php $results = $this->results(); @endphp
            <div class="manual-search__meta">
                <span>
                    {{ __('manual.search_results', [
                        'tasks' => count($results['tasks']),
                        'topics' => count($results['topics']),
                    ]) }}
                </span>
                <x-filament::link tag="button" wire:click="clearSearch">
                    {{ __('manual.search_clear') }}
                </x-filament::link>
            </div>
        @endif
    </div>

    @if ($this->isSearching())
        @php $results = $this->results(); @endphp

        @if ($results['tasks'] === [] && $results['topics'] === [])
            <p class="manual-empty">{{ __('manual.search_empty') }}</p>
        @endif

        @if ($results['tasks'] !== [])
            <section class="manual-results">
                <h2 class="manual-results__heading">{{ __('manual.tasks') }}</h2>
                <ul class="manual-results__list">
                    @foreach ($results['tasks'] as $task)
                        <li>
                            <a href="{{ ManualUrls::task($task) }}" class="manual-results__link">
                                {{ $task->title }}
                            </a>
                            <span class="manual-results__note">— {{ $task->summary }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($results['topics'] !== [])
            <section class="manual-results">
                <h2 class="manual-results__heading">{{ __('manual.reference') }}</h2>
                <ul class="manual-results__list">
                    @foreach ($results['topics'] as $topic)
                        <li>
                            <a href="{{ ManualUrls::topic($topic) }}" class="manual-results__link">
                                {{ $topic->title }}
                            </a>
                            @php $chapter = $manual->chapterOf($topic); @endphp
                            @if ($chapter !== null)
                                <span class="manual-results__note">— {{ $chapter->title }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @else
        <header class="manual-hero">
            <span class="manual-eyebrow">{{ __('manual.tasks') }}</span>
            <h1 class="manual-hero__title">{{ __('manual.tasks_heading') }}</h1>
            <p class="manual-hero__lede">{{ __('manual.tasks_intro') }}</p>
        </header>

        @foreach ($manual->taskGroups() as $group)
            <section class="manual-taskgroup">
                <h2 class="manual-taskgroup__title">{{ $group['title'] }}</h2>
                <p class="manual-taskgroup__summary">{{ $group['summary'] }}</p>

                <div class="manual-taskgrid">
                    @foreach ($group['tasks'] as $task)
                        @php $capability = $this->capabilityFor($task); @endphp
                        <a href="{{ ManualUrls::task($task) }}" class="manual-taskcard">
                            <h3 class="manual-taskcard__title">{{ $task->title }}</h3>
                            <p class="manual-taskcard__summary">{{ $task->summary }}</p>
                            <div class="manual-taskcard__meta">
                                @if ($capability === TaskCapability::PERFORM)
                                    <x-filament::badge color="success" size="sm">
                                        {{ __('manual.capability_perform') }}
                                    </x-filament::badge>
                                @elseif ($capability === TaskCapability::READ)
                                    <x-filament::badge color="gray" size="sm">
                                        {{ __('manual.capability_read') }}
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" size="sm">
                                        {{ __('manual.capability_none') }}
                                    </x-filament::badge>
                                @endif
                                <x-filament::badge color="gray" size="sm">
                                    {{ __('manual.step_count', ['count' => count($task->steps)]) }}
                                </x-filament::badge>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="manual-chaptergrid-section">
            <h2 class="manual-taskgroup__title">{{ __('manual.reference_heading') }}</h2>
            <p class="manual-taskgroup__summary">{{ __('manual.reference_intro') }}</p>

            <div class="manual-chaptergrid">
                @foreach ($manual->chapters() as $chapter)
                    <div class="manual-chaptercard">
                        <h3 class="manual-chaptercard__title">{{ $chapter->title }}</h3>
                        <ul class="manual-chaptercard__list">
                            @foreach ($chapter->topics as $topic)
                                <li>
                                    <a href="{{ ManualUrls::topic($topic) }}" class="manual-chaptercard__link">
                                        {{ $topic->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
