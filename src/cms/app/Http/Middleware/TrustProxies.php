<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Config\Config;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int $headers
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * The trusted proxies for this application.
     *
     * Every request reaches the app through a load balancer, so without a
     * trusted proxy `$request->ip()` returns that balancer's address instead
     * of the visitor's. IPAllowFilter matches the organisation allowlist
     * against exactly that value, so an allowlist would compare against the
     * balancer and never against the real client.
     *
     * @return array<int, string>|string|null
     */
    protected function proxies(): array|string|null
    {
        return Config::stringOrNull('app.trusted_proxies');
    }
}
