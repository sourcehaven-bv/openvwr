{{--
    The official OpenVWR logo from openvwr.nl: the coral mark plus the
    two-tone wordmark. Used as the panel brand (sidebar header and, via the
    simple layout, above the login card) and by the standalone auth layout.

    The wordmark's "Open" is dark blue (#164155), which disappears against the
    dark sidebar, so on dark backgrounds the mark is paired with a plain text
    wordmark that inherits the theme colour instead.
--}}
<span class="flex items-center">
    <img
        src="{{ asset('img/logo-full.svg') }}"
        alt="{{ config('app.name') }}"
        class="h-8 w-auto dark:hidden"
    >
    <span class="hidden items-center gap-x-2.5 dark:flex">
        <img src="{{ asset('img/logo.svg') }}" alt="" aria-hidden="true" class="h-8 w-8 shrink-0">
        <span class="text-xl font-bold leading-none tracking-tight text-white">
            {{ config('app.name') }}
        </span>
    </span>
</span>
