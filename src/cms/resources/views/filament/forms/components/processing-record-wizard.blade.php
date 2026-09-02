@php
    use Filament\Schemas\Components\Wizard\Step;
    $isContained = $isContained();
    $statePath = $getStatePath();
@endphp

<div
    wire:ignore.self
    x-cloak
    x-data="{
        step: null,

        nextStep: function () {
            let nextStepIndex = this.getStepIndex(this.step) + 1

            if (nextStepIndex >= this.getSteps().length) {
                return
            }

            this.step = this.getSteps()[nextStepIndex]

            this.autofocusFields()
            this.scrollToTop()
        },

        previousStep: function () {
            let previousStepIndex = this.getStepIndex(this.step) - 1

            if (previousStepIndex < 0) {
                return
            }

            this.step = this.getSteps()[previousStepIndex]

            this.autofocusFields()
            this.scrollToTop()
        },

        scrollToTop: function () {
            this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' })
        },

        autofocusFields: function () {
            $nextTick(() =>
                this.$refs[`step-${this.step}`]
                    .querySelector('[autofocus]')
                    ?.focus(),
            )
        },

        getStepIndex: function (step) {
            return this.getSteps().findIndex((indexedStep) => indexedStep === step)
        },

        getSteps: function () {
            return JSON.parse(this.$refs.stepsData.value)
        },

        isFirstStep: function () {
            return this.getStepIndex(this.step) <= 0
        },

        isLastStep: function () {
            return this.getStepIndex(this.step) + 1 >= this.getSteps().length
        },

        isStepAccessible: function (stepId) {
            return (
                @js($isSkippable()) || this.getStepIndex(this.step) > this.getStepIndex(stepId)
            )
        },

        updateQueryString: function () {
            if (! @js($isStepPersistedInQueryString())) {
                return
            }

            const url = new URL(window.location.href)
            url.searchParams.set(@js($getStepQueryStringKey()), this.step)

            history.pushState(null, document.title, url.toString())
        },
    }"
    x-init="
        $watch('step', () => updateQueryString())

        step = getSteps().at({{ $getStartStep() - 1 }})

        autofocusFields()
    "
    x-on:next-wizard-step.window="if ($event.detail.statePath === '{{ $statePath }}') nextStep()"
    {{
        $attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->merge($getExtraAlpineAttributes(), escape: false)
            ->class([
                'fi-fo-wizard',
            ])
    }}
>
    <input
        type="hidden"
        value="{{
            collect($getChildComponentContainer()->getComponents())
                ->filter(static fn (Step $step): bool => $step->isVisible())
                ->map(static fn (Step $step) => $step->getId())
                ->values()
                ->toJson()
        }}"
        x-ref="stepsData"
    />

    <style>
        .wizzard-layout {
            display: flex;
            gap: 20px;
            width: 100%;
            align-items: flex-start;
        }

        .wizzard-layout__content {
            width: 100%;
        }

        .wizzard-layout__steps > ol {
            display: flex;
            flex-direction: column;
            width: 300px;
        }

        .wizzard-layout__steps .fi-fo-wizard-header-step-label {
            text-align: left;
        }
    </style>

    <div class="wizzard-layout">
        <div
            class="wizzard-layout__content fi-contained rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            @foreach ($getChildComponentContainer()->getComponents() as $step)
                {{ $step }}
            @endforeach

            <div
                @class([
                    'flex items-center justify-between gap-x-3',
                    'px-6 pb-6' => $isContained,
                    'mt-6' => ! $isContained,
                ])
            >
        <span x-cloak x-on:click="previousStep" x-show="! isFirstStep()">
            {{ $getAction('previous') }}
        </span>

                <span x-show="isFirstStep()">
            {{ $getCancelAction() }}
        </span>

                <span
                    x-cloak
                    x-on:click="
                $wire.dispatchFormEvent(
                    'wizard::nextStep',
                    '{{ $statePath }}',
                    getStepIndex(step),
                )
            "
                    x-show="! isLastStep()"
                >
            {{ $getAction('next') }}
        </span>

                <span x-show="isLastStep()">
            {{ $getSubmitAction() }}
        </span>
            </div>

        </div>
        <div
            class="wizzard-layout__steps fi-contained rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <ol
                @if (filled($label = $getLabel()))
                    aria-label="{{ $label }}"
                @endif
                role="list"
                @class([
                    'fi-fo-wizard-header grid divide-y divide-gray-200 dark:divide-white/5 md:grid-flow-col md:divide-y-0 px-4 py-3',
                    'border-b border-gray-200 dark:border-white/10' => $isContained,
                    'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => ! $isContained,
                ])
            >
                @foreach ($getChildComponentContainer()->getComponents() as $step)
                    <li
                        class="fi-fo-wizard-header-step relative flex"
                        x-bind:class="{
                    'fi-active': getStepIndex(step) === {{ $loop->index }},
                    'fi-completed': getStepIndex(step) > {{ $loop->index }},
                }"
                    >
                        <button
                            type="button"
                            x-bind:aria-current="getStepIndex(step) === {{ $loop->index }} ? 'step' : null"
                            x-on:click="step = @js($step->getId())"
                            x-bind:disabled="! isStepAccessible(@js($step->getId()))"
                            x-bind:class="{ 'bg-gray-300 dark:bg-gray-700': getStepIndex(step) === {{ $loop->index }} }"
                            role="step"
                            class="fi-fo-wizard-header-step-button flex h-full w-full items-center gap-x-2 px-3 py-2 rounded-lg"
                        >
                            <div
                                class="fi-fo-wizard-header-step-icon-ctn flex h-5 w-5 shrink-0 items-center justify-center"
                            >
                                <x-filament::icon
                                    alias="forms::components.wizard.completed-step"
                                    icon="heroicon-o-check"
                                    x-cloak="x-cloak"
                                    x-show="getStepIndex(step) > {{ $loop->index }}"
                                    class="fi-fo-wizard-header-step-icon h-6 w-6 dark:text-primary-600 text-gray-900"
                                />
                            </div>

                            <div class="grid justify-items-start">
                                @if (! $step->isLabelHidden())
                                    <span
                                        class="fi-fo-wizard-header-step-label text-sm font-medium"
                                        x-bind:class="{
                                    'text-gray-500 dark:text-gray-400':
                                        getStepIndex(step) < {{ $loop->index }},
                                    'text-gray-950 dark:text-white': getStepIndex(step) > {{ $loop->index }},
                                }"
                                    >
                                {{ $step->getLabel() }}
                            </span>
                                @endif

                                @if (filled($description = $step->getDescription()))
                                    <span
                                        class="fi-fo-wizard-header-step-description text-start text-sm text-gray-500 dark:text-gray-400"
                                    >
                                {{ $description }}
                            </span>
                                @endif
                            </div>
                        </button>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
