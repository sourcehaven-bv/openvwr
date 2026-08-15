<?php

declare(strict_types=1);

namespace Tests\Feature\ValueObjects;

use App\ValueObjects\CalendarDate;
use Carbon\CarbonImmutable;

use function fake;
use function it;

it('can instantiate', function (): void {
    $dateTime = fake()->dateTime();

    $calendarDate = CalendarDate::instance($dateTime);

    $this->assertEquals($dateTime->format('Y-m-d'), $calendarDate->format('Y-m-d'));
});

it('can create from format', function (): void {
    $dateTime = fake()->dateTime();

    $calendarDate = CalendarDate::createFromFormat('Y-m-d', $dateTime->format('Y-m-d'));

    $this->assertEquals($dateTime->format('Y-m-d'), $calendarDate->format('Y-m-d'));
});

it('can cast to string', function (): void {
    $dateTime = fake()->dateTime();

    $calendarDate = CalendarDate::instance($dateTime);

    $this->assertEquals($dateTime->format('Y-m-d'), $calendarDate->__toString());
});

it('can add months', function (): void {
    $dateTime = fake()->calendarDate();
    $monthsToAdd = fake()->randomDigit();

    $newCalendarDate = $dateTime->addMonths($monthsToAdd);

    $this->assertEquals($dateTime->addMonths($monthsToAdd)->format('Y-m-d'), $newCalendarDate->format('Y-m-d'));
});

it('can compare equal dates', function (): void {
    $time = fake()->date();

    $date1 = CalendarDate::createFromFormat('Y-m-d', $time);
    $date2 = CalendarDate::createFromFormat('Y-m-d', $time);

    $this->assertTrue($date1->equalTo($date2));
});

it('can compare equal dates of not equal', function (): void {
    // Vaste, gegarandeerd verschillende datums. Eerder stond hier
    // fake()->date() naast fake()->unique()->date(); unique() ontdubbelt alleen
    // binnen zijn eigen reeks en kent de waarde van de eerste generator niet, dus
    // die twee konden dezelfde datum opleveren en de test viel willekeurig om.
    $date1 = CalendarDate::createFromFormat('Y-m-d', '2020-01-01');
    $date2 = CalendarDate::createFromFormat('Y-m-d', '2021-06-15');

    $this->assertFalse($date1->equalTo($date2));
});

it('can format', function (): void {
    $calendarDate = CalendarDate::instance(CarbonImmutable::create(2000, 3, 9));

    $this->assertEquals('09-03-2000', $calendarDate->format('d-m-Y'));
});

it('can handle different formats', function (string $format): void {
    $date = fake()->date();
    $calendarDate = CalendarDate::createFromFormat('Y-m-d', $date);

    $this->assertEquals($calendarDate->format($format), CarbonImmutable::parse($date)->format($format));
})->with([
    'Y-m-d',
    'd-m-Y',
    'd/m/Y',
]);

it('can is-format', function (): void {
    $calendarDate = CalendarDate::instance(CarbonImmutable::create(2000, 3, 9));

    $this->assertEquals('09 03 2000', $calendarDate->isoFormat('DD MM YYYY'));
});

it('can handle different iso-formats', function (string $format): void {
    $date = fake()->date();
    $calendarDate = CalendarDate::createFromFormat('Y-m-d', $date);

    $this->assertEquals($calendarDate->isoFormat($format), CarbonImmutable::parse($date)->isoFormat($format));
})->with([
    'D MMMM YYYY',
    'D MM Y',
]);

it('returns true for a historical date', function (): void {
    $historicalDate = CalendarDate::createFromFormat('Y-m-d', fake()->date());

    $this->assertTrue($historicalDate->isPast());
});

it('returns false for today', function (): void {
    $today = CalendarDate::instance(CarbonImmutable::now('utc'));

    $this->assertFalse($today->isPast());
});

it('returns false for a future date', function (): void {
    $futureDate = CalendarDate::createFromFormat('Y-m-d', fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'));

    $this->assertFalse($futureDate->isPast());
});

it('can parse a date', function (): void {
    $date = fake()->date();
    $calendarDate = CalendarDate::parse($date);

    $this->assertEquals($calendarDate->format('Y-m-d'), $date);
});

it('can return today', function (): void {
    $timezone = fake()->timezone();
    $today = CalendarDate::instance(CarbonImmutable::now($timezone));

    $this->assertEquals(CarbonImmutable::now($timezone)->utc()->format('Y-m-d'), $today->format('Y-m-d'));
});
