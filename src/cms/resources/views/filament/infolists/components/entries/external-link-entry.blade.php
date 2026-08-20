{{--
    Renders a location that is sometimes a URL. Filament's own entry wrapper
    emits target="_blank" without a rel (see Filament\Support\generate_href_html),
    which leaves the link open to reverse tabnabbing. Because the href comes from
    a free-text field, this view renders the anchor itself so it can carry an
    explicit rel="noopener noreferrer".
--}}
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = trim((string) $getState());
        $url = $isLinkable($state) ? $state : null;
    @endphp

    <div class="fi-in-text w-full">
        <div class="text-sm leading-6 text-gray-950 dark:text-white">
            @if ($url)
                <a
                    href="{{ $url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-primary-600 hover:underline dark:text-primary-400"
                >
                    {{ $state }}
                </a>
            @else
                {{ $state }}
            @endif
        </div>
    </div>
</x-dynamic-component>
