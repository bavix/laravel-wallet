<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

/**
 * @internal
 */
interface FormatterServiceInterface
{
    public function intValue(string|int|float $amount, int $decimalPlaces): string;

    public function floatValue(string|int|float $amount, int $decimalPlaces): string;
}
