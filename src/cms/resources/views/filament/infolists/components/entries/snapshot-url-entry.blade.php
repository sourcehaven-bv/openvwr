<div class="fi-in-entry-wrp" @class(['hidden' => !$getRecord()->snapshotSource->isPublished()])>
    <div class="grid gap-y-2">
        <div class="flex items-center justify-between gap-x-3">
            <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    {{ __('snapshot.url') }}
                </span>
            </dt>
        </div>
        <div class="grid gap-y-2">
            <dd>
                <div class="fi-in-text w-full">
                    <div class="fi-in-affixes flex">
                        <div class="min-w-0 flex-1">
                            <div class="flex gap-1.5 flex-wrap ">
                                <div class="flex">
                                    <a class="text-gray-500 dark:text-gray-400 text-sm"
                                       href="{{ $getRecord()->snapshotSource->getLatestStaticWebsiteSnapshotEntry()?->url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                    >
                                        {{ $getRecord()->snapshotSource->getLatestStaticWebsiteSnapshotEntry()?->url }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </dd>
        </div>
    </div>
</div>
