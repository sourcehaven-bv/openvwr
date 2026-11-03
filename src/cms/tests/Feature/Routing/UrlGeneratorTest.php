<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Tests\Helpers\ConfigTestHelper;

use function app;
use function expect;
use function fake;
use function it;
use function route;
use function sprintf;
use function url;

it('defaults to localhost if none given', function (): void {
    $previous = url()->previous();

    expect($previous)
        ->toBe(ConfigTestHelper::get('app.url'));
});

it('returns to session if exists', function (): void {
    $slug = fake()->slug();
    $this->get($slug);
    $previous = url()->previous();

    expect($previous)
        ->toBe(sprintf('%s/%s', ConfigTestHelper::get('app.url'), $slug));
});

it('returns to fallback if exists', function (): void {
    $slug = fake()->slug();
    $previous = url()->previous($slug);

    expect($previous)
        ->toBe(sprintf('%s/%s', ConfigTestHelper::get('app.url'), $slug));
});

it('follows the route collection when cached routes replace it', function (): void {
    url();

    $routes = new RouteCollection();
    $routes->add(new Route(['GET'], 'cached-probe', ['as' => 'cached-probe']));
    app()->instance('routes', $routes);

    expect(route('cached-probe'))
        ->toBe(sprintf('%s/cached-probe', ConfigTestHelper::get('app.url')));
});
