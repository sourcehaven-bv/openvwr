<x-grid-section :title="__('user.profile.personal_info.heading')" :description="__('user.profile.personal_info.subheading')">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    {{ __('user.profile.personal_info.submit') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-grid-section>
