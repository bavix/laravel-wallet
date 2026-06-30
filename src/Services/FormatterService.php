<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * @internal
 */
final readonly class FormatterService implements FormatterServiceInterface
{
    private RoundingMode $roundingDown;

    public function __construct()
    {
        $this->roundingDown = enum_exists(RoundingMode::class)
            ? RoundingMode::Down
            : RoundingMode::DOWN;
    }

    public function intValue(string|int|float $amount, int $decimalPlaces): string
    {
        return (string) BigDecimal::ten()
            ->power(max(0, $decimalPlaces))
            ->multipliedBy(BigDecimal::of($this->toBrick($amount)))
            ->toScale(0, $this->roundingDown);
    }

    public function floatValue(string|int|float $amount, int $decimalPlaces): string
    {
        return (string) BigDecimal::ofUnscaledValue($this->toBrick($amount), max(0, $decimalPlaces));
    }

    private function toBrick(float|int|string $value): int|string
    {
        return is_float($value) ? (string) $value : $value;
    }
}
