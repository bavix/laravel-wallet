<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Brick\Math\BigDecimal;
use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;

final readonly class MathService implements MathServiceInterface
{
    public function __construct(
        private int $scale
    ) {
    }

    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->plus(BigDecimal::of($this->toBrick($second)))
            ->toScale($scale ?? $this->scale, RoundingMode::Down);
    }

    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->minus(BigDecimal::of($this->toBrick($second)))
            ->toScale($scale ?? $this->scale, RoundingMode::Down);
    }

    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->dividedBy(BigDecimal::of($this->toBrick($second)), $scale ?? $this->scale, RoundingMode::Down);
    }

    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->multipliedBy(BigDecimal::of($this->toBrick($second)))
            ->toScale($scale ?? $this->scale, RoundingMode::Down);
    }

    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->power((int) $second)
            ->toScale($scale ?? $this->scale, RoundingMode::Down);
    }

    public function powTen(float|int|string $number): string
    {
        return $this->pow(10, $number);
    }

    public function ceil(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::Ceiling);
    }

    public function floor(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::Floor);
    }

    public function round(float|int|string $number, int $precision = 0): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HalfUp);
    }

    public function abs(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))->abs();
    }

    public function negative(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))->negated();
    }

    public function compare(float|int|string $first, float|int|string $second): int
    {
        return BigDecimal::of($this->toBrick($first))->compareTo(BigDecimal::of($this->toBrick($second)));
    }

    private function toBrick(float|int|string $value): BigNumber|int|string
    {
        return is_float($value) ? (string) $value : $value;
    }
}
