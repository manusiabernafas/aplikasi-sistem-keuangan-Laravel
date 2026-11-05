<?php

namespace App\Support;

/**
 * Exact decimal(2) arithmetic without relying on the bcmath extension.
 * Values are converted to integer cents (string-based, no float parsing)
 * so precision is never lost regardless of magnitude.
 */
class Money
{
    public static function add(string|int|float $a, string|int|float $b): string
    {
        return self::fromCents(self::toCents($a) + self::toCents($b));
    }

    public static function sub(string|int|float $a, string|int|float $b): string
    {
        return self::fromCents(self::toCents($a) - self::toCents($b));
    }

    public static function negate(string|int|float $a): string
    {
        return self::fromCents(-self::toCents($a));
    }

    /**
     * Returns -1, 0, or 1 like bccomp().
     */
    public static function compare(string|int|float $a, string|int|float $b): int
    {
        return self::toCents($a) <=> self::toCents($b);
    }

    public static function toCents(string|int|float $value): int
    {
        $value = trim((string) $value);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');

        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '0');
        $integer = $integer === '' ? '0' : $integer;
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        $cents = (int) ($integer.$fraction);

        return $negative ? -$cents : $cents;
    }

    private static function fromCents(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        $formatted = sprintf('%s%d.%02d', $negative ? '-' : '', intdiv($cents, 100), $cents % 100);

        return $formatted;
    }
}
