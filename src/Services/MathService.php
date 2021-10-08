<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\MathInterface;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Config\Repository as ConfigRepository;

class MathService implements MathInterface
{
    private int $scale;

    public function __construct(ConfigRepository $config)
    {
        $this->scale = (int) $config->get('wallet.math.scale', 64);
    }

    /**
     * {@inheritdoc}
     */
    public function add($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->plus(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function sub($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->minus(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function div($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->dividedBy(BigDecimal::of($second), $scale ?? $this->scale, RoundingMode::DOWN)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function mul($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->multipliedBy(BigDecimal::of($second))
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function pow($first, $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($first)
            ->power($second)
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function powTen($number): string
    {
        return $this->pow(10, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function ceil($number): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::CEILING)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function floor($number): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::FLOOR)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function round($number, int $precision = 0): string
    {
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HALF_UP)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function abs($number): string
    {
        return (string) BigDecimal::of($number)->abs();
    }

    /**
     * {@inheritdoc}
     */
    public function negative($number): string
    {
        return (string) BigDecimal::of($number)->negated();
    }

    /**
     * {@inheritdoc}
     */
    public function compare($first, $second): int
    {
        return BigDecimal::of($first)->compareTo(BigDecimal::of($second));
    }
}
