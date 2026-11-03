<x-grid-section md=2 :title="__('user.profile.pratique_account.heading')" :description="__('user.profile.pratique_account.subheading')">
    <x-filament::card>
        <x-filament::button tag="a" :href="$accountUrl" icon="heroicon-o-key">
            {{ __('user.profile.pratique_account.link') }}
        </x-filament::button>
    </x-filament::card>
</x-grid-section>
