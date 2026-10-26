<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Webmozart\Assert\Assert;

class CalendarDate
{
    public const string DEFAULT_STRING_FORMAT = 'Y-m-d';

    private CarbonInterface $date;

    /**
     * Keeps the calendar day as seen in the timezone of the given date.
     * Converting to UTC first would shift late-evening times in a zone ahead
     * of UTC (such as Europe/Amsterdam) back to the previous day.
     */
    private function __construct(
        CarbonInterface $date,
    ) {
        $this->date = $date->shiftTimezone('UTC')
            ->floorDay();
    }

    public static function instance(DateTimeInterface $date): self
    {
        return new self(CarbonImmutable::instance($date));
    }

    public static function createFromFormat(string $format, string $time): self
    {
        $date = CarbonImmutable::createFromFormat($format, $time);

        Assert::isInstanceOf($date, DateTimeInterface::class);

        return new self($date);
    }

    public function __toString(): string
    {
        return $this->format(self::DEFAULT_STRING_FORMAT);
    }

    public function addMonths(int $value): self
    {
        return new self($this->date->addMonths($value));
    }

    public function equalTo(CalendarDate $date): bool
    {
        return $this->date->equalTo($date->format('d-m-Y'));
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    public function isoFormat(string $format): string
    {
        return $this->date->isoFormat($format);
    }

    public function isPast(): bool
    {
        if ($this->date->isToday()) {
            return false;
        }

        return $this->date->isPast();
    }

    public static function parse(string $time): self
    {
        return self::instance(CarbonImmutable::parse($time));
    }
}
