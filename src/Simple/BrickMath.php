<?php

namespace Bavix\Wallet\Simple;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * Class BrickMath.
 */
class BrickMath extends BCMath
{
    /**
     * {@inheritdoc}
     */
    public function add($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->plus(BigDecimal::of($second))
            ->toScale($this->scale($scale), RoundingMode::DOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function sub($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->minus(BigDecimal::of($second))
            ->toScale($this->scale($scale), RoundingMode::DOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function div($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->dividedBy(BigDecimal::of($second), $this->scale($scale), RoundingMode::DOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function mul($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->multipliedBy(BigDecimal::of($second))
            ->toScale($this->scale($scale), RoundingMode::DOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function pow($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->power($second)
            ->toScale($this->scale($scale), RoundingMode::DOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function ceil($number): string
    {
        return BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::CEILING);
    }

    /**
     * {@inheritdoc}
     */
    public function floor($number): string
    {
        return BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::FLOOR);
    }

    /**
     * {@inheritdoc}
     */
    public function round($number, int $precision = 0): string
    {
        return BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HALF_UP);
    }

    /**
     * @param float|int|string $number
     * @return string
     */
    public function abs($number): string
    {
        try {
            return (string) BigDecimal::of($number)->abs();
        } catch (\Throwable $throwable) {
            return '0'; // fixme: 6.x
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compare($first, $second): int
    {
        return BigDecimal::of($first)->compareTo(BigDecimal::of($second));
    }
}
