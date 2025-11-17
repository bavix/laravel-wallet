<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Brick\Math\Exception\DivisionByZeroException;

interface MathServiceInterface
{
    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     * @return non-empty-string
     */
    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     * @return non-empty-string
     */
    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     * @return non-empty-string
     *
     * @throws DivisionByZeroException
     */
    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     * @return non-empty-string
     */
    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     * @return non-empty-string
     */
    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function powTen(float|int|string $number): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function round(float|int|string $number, int $precision = 0): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function floor(float|int|string $number): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function ceil(float|int|string $number): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function abs(float|int|string $number): string;

    /**
     * @param float|int|non-empty-string $number
     * @return non-empty-string
     */
    public function negative(float|int|string $number): string;

    /**
     * @param float|int|non-empty-string $first
     * @param float|int|non-empty-string $second
     */
    public function compare(float|int|string $first, float|int|string $second): int;
}
