{{--
    The takeover layout of the manual.

    It is Filament's own base layout, so the panel's styles, dark mode, fonts,
    scripts and the demo banner all still apply and the manual is recognisably
    the same product. What it does not include is filament()->getNavigation():
    the left column is the manual's own table of contents instead, and the
    topbar is stripped back to the title and the way out. That is the whole
    point of overriding getLayout() rather than rendering inside the panel.
--}}
@php
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div class="fi-layout manual-layout">
        <aside class="manual-sidebar" aria-label="{{ __('manual.nav_label') }}">
            <div class="manual-sidebar__inner">
                @include('filament.manual.navigation')
            </div>
        </aside>

        <div class="manual-main-ctn">
            {{--
                The stripped topbar. Everything that belongs to working in
                OpenVWR - search over records, notifications, the tenant switch,
                the user menu - is gone: in the manual none of it is what you
                came for, and leaving it would make this look like just another
                page of the panel. What stays is who you are reading (the
                organisation name, so a screenshot of the manual is still
                attributable), what you are reading, and one unmistakable way
                back.
            --}}
            <header class="manual-topbar">
                <a href="{{ $livewire->exitUrl() }}" class="manual-topbar__brand">
                    <span class="manual-topbar__mark" aria-hidden="true">V</span>
                    <span class="manual-topbar__product">
                        {{ \App\Facades\Authentication::organisation()->name }}
                    </span>
                </a>

                <span class="manual-topbar__divider" aria-hidden="true"></span>

                <span class="manual-topbar__title">{{ __('general.manual') }}</span>

                <a href="{{ $livewire->exitUrl() }}" class="manual-topbar__exit">
                    <x-filament::icon
                        icon="heroicon-m-arrow-left-on-rectangle"
                        class="manual-topbar__exit-icon"
                    />
                    <span>{{ __('manual.exit') }}</span>
                </a>
            </header>

            <main class="manual-main">
                {{ $slot }}
            </main>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire?->getRenderHookScopes()) }}
        </div>
    </div>
</x-filament-panels::layout.base>
