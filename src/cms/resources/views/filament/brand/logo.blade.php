{{--
    The official OpenVWR logo from openvwr.nl. Used as the panel brand (sidebar
    header and, via the simple layout, above the login card) and by the
    standalone auth layout.

    Two files rather than one: the logo uses #164155 for the checkmark and the
    "Open" half of the wordmark, which is all but invisible on the dark
    sidebar. The dark variant redraws those parts in white and leaves the coral
    alone, since that reads on either background.

    dark:flex rather than dark:block -- the panel stylesheet ships the former
    but not the latter, so dark:block would leave the logo hidden in dark mode.
--}}
<span class="fi-logo flex items-center">
    <img
        src="{{ asset('img/logo-full.svg') }}"
        alt="{{ config('app.name') }}"
        class="h-8 w-auto dark:hidden"
    >
    <img
        src="{{ asset('img/logo-full-dark.svg') }}"
        alt="{{ config('app.name') }}"
        class="hidden h-8 w-auto dark:flex"
    >
</span>
