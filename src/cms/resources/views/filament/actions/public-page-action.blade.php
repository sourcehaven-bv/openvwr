<span @class(['hidden' => !$getRecord()->isPublished()])>
    <a href="{{ $getRecord()->getLatestStaticWebsiteSnapshotEntry()?->url }}"
       target="_blank"
       title="{{ __('general.published_at') }}"
    >
        <x-filament::icon
            icon="heroicon-o-globe-alt"
            class="h-8 w-8 text-gray-500"
            alias="link"
            role="img"
            aria-label="{{ __('general.published_at') }}"
        />
    </a>
</span>
