<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Mathable;

/**
 * Class MathService
 * @package Bavix\Wallet\Simple
 */
class Math implements Mathable
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
        return $this->round($first + $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return string
     */
    public function sub($first, $second, ?int $scale = null): string
    {
        return $this->round($first - $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return float|int|string|null
     */
    public function div($first, $second, ?int $scale = null): string
    {
        return $this->round($first / $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return float|int|string
     */
    public function mul($first, $second, ?int $scale = null): string
    {
        return $this->round($first * $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $first
     * @param string|int|float $second
     * @param null|int $scale
     * @return string
     */
    public function pow($first, $second, ?int $scale = null): string
    {
        return $this->round($first ** $second, $this->scale($scale));
    }

    /**
     * @param string|int|float $number
     * @return string
     */
    public function ceil($number): string
    {
        return ceil($number);
    }

    /**
     * @param string|int|float $number
     * @return string
     */
    public function floor($number): string
    {
        return floor($number);
    }

    /**
     * @param float|int|string $number
     * @return string
     */
    public function abs($number): string
    {
        return abs($number);
    }

    /**
     * @param string|int|float $number
     * @param int $precision
     * @return string
     */
    public function round($number, int $precision = 0): string
    {
        return round($number, $precision);
    }

    /**
     * @param $first
     * @param $second
     * @return int
     */
    public function compare($first, $second): int
    {
        return $first <=> $second;
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
