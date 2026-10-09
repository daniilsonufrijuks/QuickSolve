<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function parse(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Amount is required.');
        }

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Enter a valid amount with up to two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    public static function optional(string $value): int
    {
        return trim($value) === '' ? 0 : self::parse($value);
    }

    public static function format(int $minor): string
    {
        $negative = $minor < 0;
        $minor = abs($minor);

        return ($negative ? '-' : '').intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function formatWithCurrency(int $minor, string $currency): string
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
        ];

        $symbol = $symbols[strtoupper($currency)] ?? strtoupper($currency).' ';

        return $symbol.self::format($minor);
    }

    /**
     * Percentage with up to two decimal places, returned as basis points (1% = 100).
     */
    public static function parsePercentToBasisPoints(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Enter a valid percentage with up to two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');
        $basisPoints = ((int) $whole * 100) + (int) $fraction;

        if ($basisPoints > 10000) {
            throw new InvalidArgumentException('Percentage cannot exceed 100.');
        }

        return $basisPoints;
    }

    public static function percentOf(int $minor, int $basisPoints): int
    {
        if ($minor < 0 || $basisPoints < 0) {
            throw new InvalidArgumentException('Amounts and percentages must be zero or positive.');
        }

        return intdiv(($minor * $basisPoints) + 5000, 10000);
    }

    public static function ratioBasisPoints(int $numerator, int $denominator): ?int
    {
        if ($denominator === 0) {
            return null;
        }

        $negative = ($numerator < 0) xor ($denominator < 0);
        $value = intdiv((abs($numerator) * 10000) + intdiv(abs($denominator), 2), abs($denominator));

        return $negative ? -$value : $value;
    }

    public static function formatBasisPoints(int $basisPoints): string
    {
        $negative = $basisPoints < 0;
        $basisPoints = abs($basisPoints);

        return ($negative ? '-' : '').intdiv($basisPoints, 100).'.'.str_pad((string) ($basisPoints % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function quantityHundredths(string $value): int
    {
        $value = trim($value);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Enter a valid quantity with up to two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');
        $hundredths = ((int) $whole * 100) + (int) $fraction;

        if ($hundredths < 1) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        return $hundredths;
    }

    public static function lineTotal(int $quantityHundredths, int $unitMinor): int
    {
        return intdiv(($quantityHundredths * $unitMinor) + 50, 100);
    }
}
