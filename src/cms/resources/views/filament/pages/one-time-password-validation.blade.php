<x-filament-panels::page.simple>
    <x-form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament::actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-form>
</x-filament-panels::page.simple>
