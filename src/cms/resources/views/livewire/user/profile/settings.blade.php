<x-grid-section :title="__('user.profile.settings.heading')" :description="__('user.profile.settings.subheading')">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    {{ __('user.profile.settings.submit') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-grid-section>
