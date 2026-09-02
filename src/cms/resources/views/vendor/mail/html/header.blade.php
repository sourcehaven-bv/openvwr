{{--
    Branded mail header. No <img>: Gmail drops SVG outright and remote images
    are blocked by default in most clients, which would leave the header
    empty. A styled wordmark on a coral rule always renders, so every login
    mail is recognisably ours even with images turned off.

    The slot is escaped, unlike the Laravel template this is based on: it only
    ever carries config('app.name'), so there is no markup to preserve and no
    reason to opt out of escaping by default.
--}}
@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<span class="brand-name">{{ $slot }}</span>
</a>
</td>
</tr>
