<?php

declare(strict_types=1);

namespace App\Import\Factories\Concerns;

use App\Config\Config;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function collect;
use function explode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function sprintf;
use function trim;

trait DataConverters
{
    /**
     * @param array<string> $options
     */
    final protected function getDescriptionFromOptions(?array $options, string $optionValue): ?string
    {
        if ($options === null) {
            return null;
        }

        foreach ($options as $option) {
            if (Str::of($option)->startsWith($optionValue)) {
                return Str::of($option)->replaceFirst(sprintf('%s: ', $optionValue), '')->toString();
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    final protected function toArray(array $data, string $key): array
    {
        $input = Arr::get($data, $key);

        if ($input === null) {
            return [];
        }
        Assert::isArray($input);

        return $input;
    }

    /**
     * @param array<string, mixed> $data
     */
    final protected function toBoolean(array $data, string $key): bool
    {
        $input = Arr::get($data, $key);

        if (is_bool($input)) {
            return $input;
        }

        return in_array($input, Config::array('import.value_converters.boolean_true'), true);
    }

    /**
     * @param array<string, mixed> $data
     */
    final protected function toCarbon(array $data, string $key, ?string $format = null): CarbonImmutable
    {
        $input = Arr::get($data, $key);
        Assert::string($input);

        if ($format !== null) {
            return $this->convertToCarbon($input, $format);
        }

        $expectedFormats = Config::array('import.date.expectedFormats');
        foreach ($expectedFormats as $expectedFormat) {
            Assert::string($expectedFormat);

            try {
                return $this->convertToCarbon($input, $expectedFormat);
            } catch (InvalidFormatException) {
                // pass, will try next expected format
            }
        }

        throw new InvalidFormatException(
            sprintf('Could not parse date %s using expected formats [%s]', $input, Arr::join($expectedFormats, ', ')),
        );
    }

    /**
     * Optional dates: a missing value is not an error, unlike toCarbon().
     *
     * @param array<string, mixed> $data
     */
    final protected function toCarbonOrNull(array $data, string $key, ?string $format = null): ?CarbonImmutable
    {
        if (Arr::get($data, $key) === null) {
            return null;
        }

        return $this->toCarbon($data, $key, $format);
    }

    /**
     * Calendar dates (`date` columns) carry no time, so they must not be shifted
     * to UTC the way toCarbon() shifts timestamps: midnight in Europe/Amsterdam
     * would otherwise become the previous day.
     *
     * @param array<string, mixed> $data
     */
    final protected function toCalendarDateOrNull(array $data, string $key, ?string $format = null): ?CarbonImmutable
    {
        $date = $this->toCarbonOrNull($data, $key, $format);

        if ($date === null) {
            return null;
        }

        return $date->setTimezone(Config::string('import.date.timezone'))->startOfDay();
    }

    /**
     * Multi-value fields are stored as arrays. A spreadsheet cannot hold a list,
     * so a single cell is split on newlines.
     *
     * @param array<string, mixed> $data
     * @param non-empty-string $separator
     *
     * @return array<int, string>|null
     */
    final protected function toStringList(array $data, string $key, string $separator = "\n"): ?array
    {
        $input = Arr::get($data, $key);

        if ($input === null) {
            return null;
        }

        if (!is_array($input)) {
            Assert::scalar($input);
            $input = explode($separator, (string) $input);
        }

        $values = [];
        foreach ($input as $value) {
            Assert::scalar($value);
            $value = trim((string) $value);

            if ($value !== '') {
                $values[] = $value;
            }
        }

        return $values === [] ? null : $values;
    }

    /**
     * @param array<array-key, mixed>|null $input
     */
    final protected function toImplodedString(?array $input): string
    {
        if ($input === null) {
            return '';
        }

        return collect($input)->implode("\n");
    }

    /**
     * @param array<string, mixed> $data
     */
    final protected function toInt(array $data, string $key): int
    {
        $input = Arr::get($data, $key);
        Assert::integer($input);

        return $input;
    }

    /**
     * @param array<string, mixed> $data
     */
    final protected function toString(array $data, string $key): string
    {
        $input = Arr::get($data, $key);

        if ($input === null) {
            return '';
        }

        if (is_array($input)) {
            return $this->toImplodedString($input);
        }

        Assert::scalar($input);

        return (string) $input;
    }

    /**
     * @param array<string, mixed> $data
     */
    final protected function toStringOrNull(array $data, string $key): ?string
    {
        $input = Arr::get($data, $key);

        if ($input === null) {
            return null;
        }

        if (is_string($input) || is_int($input)) {
            return (string) $input;
        }

        Assert::isArray($input);

        return collect($input)->implode("\n");
    }

    private function convertToCarbon(string $input, string $format): CarbonImmutable
    {
        $date = CarbonImmutable::createFromFormat(
            $format,
            $input,
            Config::string('import.date.timezone'),
        );
        Assert::isInstanceOf($date, CarbonImmutable::class);

        return $date->utc();
    }
}
