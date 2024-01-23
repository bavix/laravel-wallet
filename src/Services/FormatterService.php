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
            ->power($decimalPlaces)
            ->multipliedBy(BigDecimal::of($amount))
            ->toScale(0, RoundingMode::DOWN);
    }

    public function floatValue(string|int|float $amount, int $decimalPlaces): string
    {
        return (string) BigDecimal::ofUnscaledValue($amount, $decimalPlaces);
    }
}
