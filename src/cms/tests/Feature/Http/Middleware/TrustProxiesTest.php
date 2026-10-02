<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\ConfigTestHelper;

use function expect;
use function it;

/**
 * The application sits behind a load balancer, so `$request->ip()` returns that
 * balancer unless it is trusted. IPAllowFilter matches the organisation
 * allowlist against that value, so these tests pin down which address wins.
 */
function handleThroughProxy(string $proxy, string $forwardedFor): Request
{
    $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => $proxy]);
    $request->headers->set('X-Forwarded-For', $forwardedFor);

    $middleware = new TrustProxies();
    $middleware->handle($request, static function (Request $request): Response {
        return new Response('OK');
    });

    return $request;
}

it('resolves the visitor address when the request comes through a trusted proxy', function (): void {
    ConfigTestHelper::set('app.trusted_proxies', '10.0.0.35');

    $request = handleThroughProxy('10.0.0.35', '203.0.113.7');

    expect($request->ip())
        ->toBe('203.0.113.7');
});

it('accepts a list of proxies', function (): void {
    ConfigTestHelper::set('app.trusted_proxies', '10.0.0.34, 10.0.0.35');

    $request = handleThroughProxy('10.0.0.35', '203.0.113.7');

    expect($request->ip())
        ->toBe('203.0.113.7');
});

it('accepts a CIDR range', function (): void {
    ConfigTestHelper::set('app.trusted_proxies', '10.0.0.0/24');

    $request = handleThroughProxy('10.0.0.35', '203.0.113.7');

    expect($request->ip())
        ->toBe('203.0.113.7');
});

it('keeps the calling address when the forwarded header comes from an untrusted host', function (): void {
    ConfigTestHelper::set('app.trusted_proxies', '10.0.0.35');

    $request = handleThroughProxy('198.51.100.9', '203.0.113.7');

    expect($request->ip())
        ->toBe('198.51.100.9');
});

it('ignores the forwarded header when no proxy is configured', function (): void {
    ConfigTestHelper::set('app.trusted_proxies', null);

    $request = handleThroughProxy('10.0.0.35', '203.0.113.7');

    expect($request->ip())
        ->toBe('10.0.0.35');
});
