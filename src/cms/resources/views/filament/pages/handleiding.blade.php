@php
    use App\Manual\TaskCapability;

    $manual = $this->manual();
    $roles = $this->roles();
@endphp

<x-filament-panels::page>
    <div class="mx-auto w-full max-w-5xl space-y-8">

        {{-- Search across both layers --}}
        <div class="manual-search space-y-3">
            <x-filament::input.wrapper
                :prefix-icon="'heroicon-m-magnifying-glass'"
                class="w-full"
            >
                <x-filament::input
                    type="search"
                    wire:model.live.debounce.250ms="search"
                    :placeholder="__('manual.search_placeholder')"
                />
            </x-filament::input.wrapper>

            @if ($this->isSearching())
                @php $results = $this->results(); @endphp
                <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
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
                <x-filament::section>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('manual.search_empty') }}
                    </p>
                </x-filament::section>
            @endif

            @if ($results['tasks'] !== [])
                <x-filament::section>
                    <x-slot name="heading">{{ __('manual.tasks') }}</x-slot>
                    <ul class="space-y-2">
                        @foreach ($results['tasks'] as $task)
                            <li>
                                <a href="#taak-{{ $task->id }}"
                                   class="font-medium text-primary-600 hover:underline dark:text-primary-400">
                                    {{ $task->title }}
                                </a>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    — {{ $task->summary }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif

            @if ($results['topics'] !== [])
                <x-filament::section>
                    <x-slot name="heading">{{ __('manual.reference') }}</x-slot>
                    <ul class="space-y-2">
                        @foreach ($results['topics'] as $topic)
                            <li>
                                <a href="#{{ $topic->id }}"
                                   class="font-medium text-primary-600 hover:underline dark:text-primary-400">
                                    {{ $topic->title }}
                                </a>
                                @php $chapter = $manual->chapterOf($topic); @endphp
                                @if ($chapter !== null)
                                    <span class="text-sm text-gray-500 dark:text-gray-500">
                                        — {{ $chapter->title }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif
        @else

            {{-- ============ LAYER 1: TAKEN ============ --}}
            <section class="space-y-4">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ __('manual.tasks_heading') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('manual.tasks_intro') }}
                    </p>
                </div>

                @foreach ($manual->taskGroups() as $group)
                    <x-filament::section>
                        <x-slot name="heading">{{ $group['title'] }}</x-slot>
                        <x-slot name="description">{{ $group['summary'] }}</x-slot>

                        <div class="manual-grid manual-grid--tasks">
                            @foreach ($group['tasks'] as $task)
                                @php $capability = $this->capabilityFor($task); @endphp
                                <a href="#taak-{{ $task->id }}"
                                   class="manual-card">
                                    <h3 class="font-semibold text-gray-950 dark:text-white">
                                        {{ $task->title }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $task->summary }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
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
                    </x-filament::section>
                @endforeach
            </section>

            {{-- Task detail --}}
            <section class="space-y-4">
                @foreach ($manual->tasks() as $task)
                    @php $capability = $this->capabilityFor($task); @endphp
                    <x-filament::section id="taak-{{ $task->id }}" collapsible collapsed>
                        <x-slot name="heading">{{ $task->title }}</x-slot>
                        <x-slot name="description">{{ $task->summary }}</x-slot>

                        <div class="space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $task->intro }}</p>

                            @if ($capability === TaskCapability::PERFORM)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('manual.role_can_perform') }}
                                </p>
                            @elseif ($capability === TaskCapability::READ)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('manual.role_can_read') }}
                                </p>
                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('manual.role_cannot') }}
                                    <a href="#rollen" class="text-primary-600 hover:underline dark:text-primary-400">
                                        {{ __('manual.role_see_roles') }}
                                    </a>
                                </p>
                            @endif

                            <ol class="space-y-4">
                                @foreach ($task->steps as $index => $step)
                                    <li class="flex gap-3">
                                        <span class="mt-0.5 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="space-y-1">
                                            <h4 class="font-medium text-gray-950 dark:text-white">
                                                {{ $step->title }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $step->body }}
                                            </p>
                                            @php
                                                $stepTopics = array_filter(array_map(
                                                    fn (string $id) => $manual->topic($id),
                                                    $step->topicIds,
                                                ));
                                            @endphp
                                            @if ($stepTopics !== [])
                                                <p class="text-sm">
                                                    <span class="text-gray-500 dark:text-gray-500">
                                                        {{ __('manual.see_reference') }}
                                                    </span>
                                                    @foreach ($stepTopics as $topic)
                                                        <a href="#{{ $topic->id }}"
                                                           class="ml-1 text-primary-600 hover:underline dark:text-primary-400">
                                                            {{ $topic->title }}
                                                        </a>
                                                    @endforeach
                                                </p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>

                            @if ($task->done !== null)
                                <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                    <span class="font-medium">{{ __('manual.done') }}:</span>
                                    {{ $task->done }}
                                </div>
                            @endif
                        </div>
                    </x-filament::section>
                @endforeach
            </section>

            {{-- ============ LAYER 2: NASLAG ============ --}}
            <section class="space-y-4">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ __('manual.reference_heading') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('manual.reference_intro') }}
                    </p>
                </div>

                {{-- Browsable overview of every chapter and topic --}}
                <x-filament::section>
                    <x-slot name="heading">{{ __('manual.overview_heading') }}</x-slot>
                    <x-slot name="description">{{ __('manual.overview_intro') }}</x-slot>

                    <div class="manual-grid manual-grid--chapters">
                        @foreach ($manual->chapters() as $chapter)
                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $chapter->title }}
                                </h3>
                                <ul class="space-y-0.5">
                                    @foreach ($chapter->topics as $topic)
                                        <li>
                                            <a href="#{{ $topic->id }}"
                                               class="text-sm text-primary-600 hover:underline dark:text-primary-400">
                                                {{ $topic->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>

                @foreach ($manual->chapters() as $chapter)
                    <x-filament::section id="{{ $chapter->id }}">
                        <x-slot name="heading">{{ $chapter->title }}</x-slot>
                        <x-slot name="description">{{ $chapter->summary }}</x-slot>

                        <div class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($chapter->topics as $topic)
                                <article id="{{ $topic->id }}" class="manual-topic py-6 first:pt-0 last:pb-0">
                                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                        {{ $topic->title }}
                                    </h3>

                                    @if ($topic->availability !== null)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">
                                            <span class="font-medium">{{ __('manual.available_for') }}:</span>
                                            {{ $topic->availability }}
                                        </p>
                                    @endif

                                    <div class="manual-prose mt-3">
                                        {!! $topic->html() !!}
                                    </div>

                                    @php $usedIn = $manual->tasksUsing($topic); @endphp
                                    <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                                        <h4 class="text-sm font-medium text-gray-950 dark:text-white">
                                            {{ __('manual.used_in_tasks') }}
                                        </h4>
                                        @if ($usedIn === [])
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {{ __('manual.used_in_no_tasks') }}
                                            </p>
                                        @else
                                            <ul class="mt-1 space-y-0.5">
                                                @foreach ($usedIn as $task)
                                                    <li>
                                                        <a href="#taak-{{ $task->id }}"
                                                           class="text-sm text-primary-600 hover:underline dark:text-primary-400">
                                                            {{ $task->title }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </x-filament::section>
                @endforeach
            </section>
        @endif
    </div>
</x-filament-panels::page>
