@php
    use App\Config\Config;
    use App\Enums\BannerLevel;

    $message = trim(Config::stringOrNull('banner.message') ?? '');
    // An unknown level is an operator typo, not a reason to break every page.
    $level = BannerLevel::tryFrom(Config::string('banner.level')) ?? BannerLevel::WARNING;
@endphp

@if ($message !== '')
    <div role="status" class="fi-banner {{ $level->cssClass() }}">
        {{ $message }}
    </div>
@endif
