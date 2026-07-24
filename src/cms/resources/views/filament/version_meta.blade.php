@php
    $label = config('version.label');
    $sha = config('version.sha');
    $version = $sha ? sprintf('%s (%s)', $label, $sha) : $label;
@endphp
<meta name="app-version" content="{{ $version }}">
