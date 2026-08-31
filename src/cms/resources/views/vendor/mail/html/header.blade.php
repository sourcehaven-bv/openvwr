{{--
    Branded mail header: the logo as a PNG.

    PNG rather than SVG -- Gmail drops SVG entirely. The alt text carries the
    brand name, so clients that block remote images by default still show
    "OpenVWR" where the logo would be. The image is flattened onto white so it
    stays legible in dark-mode clients that would otherwise place it on a dark
    card.

    Width/height are set as attributes as well as in CSS: Outlook ignores the
    stylesheet and would otherwise render the image at its full 2x pixel size.
--}}
@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('img/logo-full@2x.png') }}"
     width="179" height="48"
     alt="{{ config('app.name') }}"
     class="brand-logo">
</a>
</td>
</tr>
