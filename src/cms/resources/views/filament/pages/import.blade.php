<x-filament-panels::page>
    {{ __('import.help') }}
    <x-form wire:submit="submit">
        {{ $this->form }}
        <div>
            <x-filament::button type="submit" size="sm">
                Import
            </x-filament::button>
        </div>
    </x-form>
</x-filament-panels::page>
