<?php

namespace Bavix\Wallet\Interfaces;

interface Mathable
{
    /**
     * @param int|float|string $first
     * @param int|float|string $second
     * @param int|null $scale
     * @return string
     */
    public function add($first, $second, ?int $scale = null): string;

    /**
     * @param int|float|string $first
     * @param int|float|string $second
     * @param int|null $scale
     * @return string
     */
    public function sub($first, $second, ?int $scale = null): string;

    /**
     * @param int|float|string $first
     * @param int|float|string $second
     * @param int|null $scale
     * @return string
     */
    public function div($first, $second, ?int $scale = null): string;

    /**
     * @param int|float|string $first
     * @param int|float|string $second
     * @param int|null $scale
     * @return string
     */
    public function mul($first, $second, ?int $scale = null): string;

    /**
     * @param int|float|string $first
     * @param int|string $second
     * @param int|null $scale
     * @return string
     */
    public function pow($first, $second, ?int $scale = null): string;

    /**
     * @param int|float|string $number
     * @param int $precision
     * @return string
     */
    public function round($number, int $precision = 0): string;

    /**
     * @param int|float|string $number
     * @return string
     */
    public function floor($number): string;

    /**
     * @param int|float|string $number
     * @return string
     */
    public function ceil($number): string;

    /**
     * @param int|float|string $number
     * @return string
     */
    public function abs($number): string;

    /**
     * @param int|float|string $number
     * @return string
     */
    public function negative($number): string;

    /**
     * @param int|float|string $first
     * @param int|float|string $second
     * @return int
     */
    public function compare($first, $second): int;
}
