@php
    /** @var App\Models\User $user */
    use App\Facades\Otp;
@endphp
<x-grid-section md=2 :title="__('user.profile.one_time_password.title')"
                :description="__('user.profile.one_time_password.description')">
    <x-filament::card>
        <div class="mb-5">
            <div class="flex">
                <h3 class="flex items-center gap-2 text-lg font-medium ">
                    @svg('heroicon-s-shield-exclamation', 'w-6 h-6 text-danger-600')
                    {{ __('user.profile.one_time_password.must_enable') }}
                </h3>
            </div>
        </div>

        @unless (Otp::hasOtpEnabled($user))
            <h3 class="flex items-center gap-2 text-lg font-medium">
                @svg('heroicon-o-exclamation-circle', 'w-6')
                {{__('user.profile.one_time_password.not_enabled.title') }}
            </h3>
            <p class="text-sm">{{ __('user.profile.one_time_password.not_enabled.description') }}</p>

            <div class="flex justify-between mt-3">
                {{ $this->enableAction }}
            </div>
        @else
            @if (Otp::hasOtpConfirmed($user))
                <h3 class="flex items-center gap-2 text-lg font-medium">
                    @svg('heroicon-o-shield-check', 'w-6')
                    {{ __('user.profile.one_time_password.enabled.title') }}
                </h3>
                <p class="text-sm">{{ __('user.profile.one_time_password.enabled.description') }}</p>
                <div class="flex justify-between mt-3">
                    {{ $this->regenerateCodesAction }}
                    {{ $this->disableAction()->color('danger') }}
                </div>
            @else
                <h3 class="flex items-center gap-2 text-lg font-medium">
                    @svg('heroicon-o-question-mark-circle', 'w-6')
                    {{ __('user.profile.one_time_password.finish_enabling.title') }}
                </h3>
                <p class="text-sm">{{ __('user.profile.one_time_password.finish_enabling.description') }}</p>
                <div class="flex mt-3 space-x-4 divide-x">
                    <div class="px-4 space-y-3 w-1/2">
                        {{-- data-screenshot-mask: the handleiding has a figure of
                             this card, and its images are served unauthenticated.
                             tools/screenshots masks these two elements so no
                             scannable code or key is ever published. --}}
                        <div class="flex place-content-center mt-3" data-screenshot-mask="qr">
                            {!! $this->getQrCode() !!}
                        </div>
                        <p class="pt-2 text-sm break-words" data-screenshot-mask="secret">
                            {{ __('user.profile.one_time_password.setup_key') }}
                            {{ $user->otp_secret }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-between mt-3">
                    {{ $this->confirmAction }}
                    {{ $this->disableAction }}
                </div>

            @endif

        @endunless
    </x-filament::card>
    <x-filament-actions::modals/>
</x-grid-section>
