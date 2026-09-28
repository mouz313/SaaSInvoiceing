<?php

namespace App\Support;

class Currency
{
    /**
     * Supported currencies definition.
     *
     * @var array<string, array{name: string, symbol: string, precision: int}>
     */
    protected static array $currencies = [
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => 'Rs', 'precision' => 2],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'precision' => 2],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'precision' => 2],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'precision' => 2],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'AED', 'precision' => 2],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR', 'precision' => 2],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'CA$', 'precision' => 2],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'AU$', 'precision' => 2],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'precision' => 2],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'precision' => 0],
        'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'precision' => 2],
    ];

    /**
     * Get all supported currencies.
     *
     * @return array<string, array{name: string, symbol: string, precision: int}>
     */
    public static function all(): array
    {
        return static::$currencies;
    }

    /**
     * Get symbol for given currency code.
     */
    public static function symbol(?string $code = 'PKR'): string
    {
        $code = strtoupper($code ?: 'PKR');

        return static::$currencies[$code]['symbol'] ?? $code;
    }

    /**
     * Get name for given currency code.
     */
    public static function name(?string $code = 'PKR'): string
    {
        $code = strtoupper($code ?: 'PKR');

        return static::$currencies[$code]['name'] ?? $code;
    }

    /**
     * Format a monetary amount with currency symbol.
     */
    public static function format(float|int|string $amount, ?string $code = 'PKR', ?int $decimals = null): string
    {
        $code = strtoupper($code ?: 'PKR');
        $precision = $decimals ?? (static::$currencies[$code]['precision'] ?? 2);
        $symbol = static::symbol($code);

        $formatted = number_format((float) $amount, $precision);

        return "{$symbol} {$formatted}";
    }
}
