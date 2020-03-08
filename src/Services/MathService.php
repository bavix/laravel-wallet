<?php

namespace Bavix\Wallet\Services;

/**
 * Class MathService
 * @package Bavix\Wallet\Services
 * @codeCoverageIgnore
 */
class MathService
{

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @return string
     */
    public function add($first, $second)
    {
        if (config('wallet.bcmath.enabled')) {
            return bcadd($first, $second, config('wallet.bcmath.scale', 16));
        }

        return $first + $second;
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @return string
     */
    public function sub($first, $second): string
    {
        if (config('wallet.bcmath.enabled')) {
            return bcsub($first, $second, config('wallet.bcmath.scale', 16));
        }

        return $first - $second;
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @return float|int|string|null
     */
    public function div($first, $second): string
    {
        if (config('wallet.bcmath.enabled')) {
            return bcdiv($first, $second, config('wallet.bcmath.scale', 16));
        }

        return $first / $second;
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @return float|int|string
     */
    public function mul($first, $second): string
    {
        if (config('wallet.bcmath.enabled')) {
            return bcmul($first, $second, config('wallet.bcmath.scale', 16));
        }

        return $first * $second;
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @return string
     */
    public function pow($first, $second): string
    {
        if (config('wallet.bcmath.enabled')) {
            return bcpow($first, $second, config('wallet.bcmath.scale', 16));
        }

        return $first ** $second;
    }

}
