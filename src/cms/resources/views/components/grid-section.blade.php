{{--
    A titled section with its explanation beside its fields.

    Used to be built on <x-filament::grid>, which v5 moved into the schemas
    package where it renders a schema's own children and can no longer wrap
    arbitrary markup. The layout was only ever a two column grid, so it is
    written out here rather than borrowed from a component that no longer
    means the same thing.
--}}
@props(['title', 'description'])

<div {{ $attributes->class(['filament-breezy-grid-section grid grid-cols-1 gap-4 pt-6 md:grid-cols-2']) }}>
    <div>
        <h3 class="filament-breezy-grid-title text-lg font-medium">{{ $title }}</h3>

        <p class="filament-breezy-grid-description mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>
