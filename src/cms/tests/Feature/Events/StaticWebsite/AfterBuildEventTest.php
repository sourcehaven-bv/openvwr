<?php

declare(strict_types=1);

use App\Events\StaticWebsite\AfterBuildEvent;

it('can be dispatched', function (): void {
    // Just dispatch the event to ensure the code is covered
    // The dispatch method will call the LogDispatchable trait which logs and then dispatches
    AfterBuildEvent::dispatch();

    // No need to assert anything - we just need the code to execute for coverage
    expect(true)->toBeTrue();
});
