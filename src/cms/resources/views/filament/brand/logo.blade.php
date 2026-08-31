{{--
    Brand lockup: mark plus wordmark. Used as the panel brand (sidebar header
    and, via the simple layout, above the login card) and by the standalone
    auth layout, so it has to hold up on the dark sidebar as well as on a
    white card. The wordmark is a span rather than SVG text: it stays in the
    panel font and inherits the right colour in each theme.
--}}
<span class="flex items-center gap-x-2.5">
    <img
        src="{{ asset('img/logo.svg') }}"
        alt=""
        aria-hidden="true"
        class="h-8 w-8 shrink-0"
    >
    <span class="text-xl font-bold leading-none tracking-tight text-gray-950 dark:text-white">
        {{ config('app.name') }}
    </span>
</span>
