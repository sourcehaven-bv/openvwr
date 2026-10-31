<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The Pratique webhook endpoint is exempt.
     *
     * CSRF protects a browser session from being used by another origin; this
     * route has no session to protect and no browser involved. It is called
     * server-to-server by the proxy, which cannot hold a token, and it
     * authenticates the delivery by verifying the ES256 signature over the whole
     * request body — a far stronger check than a CSRF token.
     *
     * @var array<int, string> $except
     */
    protected $except = [
        'pratique/webhook',
    ];

    protected $addHttpCookie = false;
}
