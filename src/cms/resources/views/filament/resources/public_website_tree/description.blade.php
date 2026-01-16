<span class="font-mono">{{ $publicWebsiteTree->slug }}</span>
{{ $publicWebsiteTree->organisation?->name }}
@if($publicWebsiteTree->public_from === null)
    {{ __('public_website_tree.public_from_null') }}
@elseif($publicWebsiteTree->public_from->isFuture())
    {{ __('public_website_tree.public_from_future', ['publicationDate' => DateFormat::toDate($publicWebsiteTree->public_from)]) }}
@endif
