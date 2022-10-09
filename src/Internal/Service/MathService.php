<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class MathService implements MathServiceInterface
{
    public function __construct(
        private int $scale
    ) {
    }

    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->plus(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
        ;
    }

    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->minus(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
        ;
    }

    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->dividedBy(BigDecimal::of($second), $scale ?? $this->scale, RoundingMode::DOWN)
        ;
    }

    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->multipliedBy(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
        ;
    }

    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->power((int) $second)
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
        ;
    }

    public function powTen(float|int|string $number): string
    {
        return $this->pow(10, $number);
    }

    public function ceil(float|int|string $number): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::CEILING)
        ;
    }

    public function floor(float|int|string $number): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::FLOOR)
        ;
    }

    public function round(float|int|string $number, int $precision = 0): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HALF_UP)
        ;
    }

    public function abs(float|int|string $number): string
    {
        return (string) BigDecimal::of($number)->abs();
    }

    public function negative(float|int|string $number): string
    {
        return (string) BigDecimal::of($number)->negated();
    }

    public function compare(float|int|string $first, float|int|string $second): int
    {
        return BigDecimal::of($first)->compareTo(BigDecimal::of($second));
    }
}
