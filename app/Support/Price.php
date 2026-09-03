<?php

namespace App\Support;

final class Price
{
    /**
     * @return list<string>
     */
    public static function vatRates(): array
    {
        return ['0', '1', '8', '10', '18', '20'];
    }

    public static function money(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '0.00';
        }

        return bcadd((string) $value, '0', 2);
    }

    public static function rate(mixed $value, string $fallback = '20'): string
    {
        if (! is_numeric($value)) {
            return self::money($fallback);
        }

        $rate = self::money($value);

        return bccomp($rate, '0', 2) < 0 || bccomp($rate, '100', 2) > 0
            ? self::money($fallback)
            : $rate;
    }

    /**
     * @return array{gross: string, net: string, vat: string, rate: string}
     */
    public static function breakdown(mixed $gross, mixed $rate): array
    {
        $gross = self::money($gross);
        $rate = self::rate($rate);
        $divisor = bcadd('100', $rate, 4);
        $net = bccomp($divisor, '0', 4) === 0
            ? $gross
            : bcdiv(bcmul($gross, '100', 4), $divisor, 2);
        $vat = bcsub($gross, $net, 2);

        return [
            'gross' => $gross,
            'net' => $net,
            'vat' => $vat,
            'rate' => $rate,
        ];
    }
}
