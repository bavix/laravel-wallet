<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

interface MathInterface
{
    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function add($first, $second, ?int $scale = null): string;

    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function sub($first, $second, ?int $scale = null): string;

    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function div($first, $second, ?int $scale = null): string;

    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function mul($first, $second, ?int $scale = null): string;

    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function pow($first, $second, ?int $scale = null): string;

    /** @param float|int|string $number */
    public function powTen($number): string;

    /**
     * @param float|int|string $number
     */
    public function round($number, int $precision = 0): string;

    /**
     * @param float|int|string $number
     */
    public function floor($number): string;

    /**
     * @param float|int|string $number
     */
    public function ceil($number): string;

    /**
     * @param float|int|string $number
     */
    public function abs($number): string;

    /**
     * @param float|int|string $number
     */
    public function negative($number): string;

    /**
     * @param float|int|string $first
     * @param float|int|string $second
     */
    public function compare($first, $second): int;
}
