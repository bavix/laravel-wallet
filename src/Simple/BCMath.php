<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Mathable;

/**
 * Class MathService
 * @package Bavix\Wallet\Services
 * @codeCoverageIgnore
 */
class BCMath implements Mathable
{

    /**
     * @var int
     */
    protected $scale;

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return string
     */
    public function add($first, $second, ?int $scale = null): string
    {
        return bcadd($first, $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return string
     */
    public function sub($first, $second, ?int $scale = null): string
    {
        return bcsub($first, $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return float|int|string|null
     */
    public function div($first, $second, ?int $scale = null): string
    {
        return bcdiv($first, $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return float|int|string
     */
    public function mul($first, $second, ?int $scale = null): string
    {
        return bcmul($first, $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return string
     */
    public function pow($first, $second, ?int $scale = null): string
    {
        return bcpow($first, $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $number
     * @return string
     */
    public function ceil($number): string
    {
        if (strpos($number, '.') !== false) {
            if (preg_match("~\.[0]+$~", $number)) {
                return $this->bcround($number, 0);
            }
            if ($number[0] !== '-') {
                return bcadd($number, 1, 0);
            }
            return bcsub($number, 0, 0);
        }
        return $number;
    }

    /**
     * @param string|int|float $number
     * @return string
     */
    public function floor($number): string
    {
        if (strpos($number, '.') === false) {
            return $number;
        }

        if (preg_match("~\.[0]+$~", $number)) {
            return $this->round($number, 0);
        }

        if ($number[0] !== '-') {
            return bcadd($number, 0, 0);
        }

        return bcsub($number, 1, 0);
    }

    /**
     * @param string|int|float $number
     * @param int $precision
     * @return string
     */
    public function round($number, int $precision = 0): string
    {
        if (strpos($number, '.') === false) {
            return $number;
        }

        if ($number[0] !== '-') {
            return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
    }

    /**
     * @param $first
     * @param $second
     * @return int
     */
    public function compare($first, $second): int
    {
        return bccomp($first, $second, $this->scale());
    }

    /**
     * @param int $scale
     * @return int
     */
    protected function scale(?int $scale = null): int
    {
        if ($scale !== null) {
            return $scale;
        }

        if ($this->scale === null) {
            $this->scale = (int)config('wallet.math.scale', 64);
        }

        return $this->scale;
    }

}
