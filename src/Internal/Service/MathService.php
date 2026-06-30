<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final readonly class MathService implements MathServiceInterface
{
    private array $roundingModes;

    public function __construct(
        private int $scale
    ) {
        $this->roundingModes = $this->detectRoundingModes();
    }

    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->plus(BigDecimal::of($this->toBrick($second)))
            ->toScale($this->positiveScale($scale), $this->roundingModes['down']);
    }

    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->minus(BigDecimal::of($this->toBrick($second)))
            ->toScale($this->positiveScale($scale), $this->roundingModes['down']);
    }

    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->dividedBy(
                BigDecimal::of($this->toBrick($second)),
                $this->positiveScale($scale),
                $this->roundingModes['down']
            );
    }

    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->multipliedBy(BigDecimal::of($this->toBrick($second)))
            ->toScale($this->positiveScale($scale), $this->roundingModes['down']);
    }

    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        return (string) BigDecimal::of($this->toBrick($first))
            ->power($this->positiveScale((int) $second))
            ->toScale($this->positiveScale($scale), $this->roundingModes['down']);
    }

    public function powTen(float|int|string $number): string
    {
        return $this->pow(10, $number);
    }

    public function ceil(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), 0, $this->roundingModes['ceiling']);
    }

    public function floor(float|int|string $number): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), 0, $this->roundingModes['floor']);
    }

    public function round(float|int|string $number, int $precision = 0): string
    {
        return (string) BigDecimal::of($this->toBrick($number))
            ->dividedBy(BigDecimal::one(), $this->positiveScale($precision), $this->roundingModes['halfUp']);
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

    private function positiveScale(?int $scale): int
    {
        return max(0, $scale ?? $this->scale);
    }

    private function toBrick(float|int|string $value): int|string
    {
        return is_float($value) ? (string) $value : $value;
    }

    private function detectRoundingModes(): array
    {
        if (enum_exists(RoundingMode::class)) {
            return [
                'down' => RoundingMode::Down,
                'ceiling' => RoundingMode::Ceiling,
                'floor' => RoundingMode::Floor,
                'halfUp' => RoundingMode::HalfUp,
            ];
        }

        return [
            'down' => RoundingMode::DOWN,
            'ceiling' => RoundingMode::CEILING,
            'floor' => RoundingMode::FLOOR,
            'halfUp' => RoundingMode::HALF_UP,
        ];
    }
}
