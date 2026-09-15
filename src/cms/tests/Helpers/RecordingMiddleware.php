<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Closure;
use Illuminate\Http\Request;

/** The one middleware RecordingAuthenticationStrategy names as its gate. */
class RecordingMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        RecordingAuthenticationStrategy::$ran = true;

        return $next($request);
    }
}
