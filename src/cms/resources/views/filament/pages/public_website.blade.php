<x-filament-panels::page>
    <x-form wire:submit="save">
        {{ $this->form }}

        <x-filament::actions
            :actions="$this->getFormActions()"
        />
    </x-form>
</x-filament-panels::page>
