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
    public function intValue(string|int|float $amount, int $decimalPlaces): string
    {
        return (string) BigDecimal::ten()
            ->power(max(0, $decimalPlaces))
            ->multipliedBy(BigDecimal::of($this->toBrick($amount)))
            ->toScale(0, RoundingMode::Down);
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
