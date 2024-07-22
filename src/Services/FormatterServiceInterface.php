<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

/**
 * @api
 */
interface FormatterServiceInterface
{
    /**
     * Convert an amount to an integer value with given decimal places.
     *
     * @param string|int|float $amount The amount to convert.
     * @param int $decimalPlaces The number of decimal places.
     * @return string The integer value of the amount.
     */
    public function intValue(string|int|float $amount, int $decimalPlaces): string;

    /**
     * Convert an amount to a float value with given decimal places.
     *
     * @param string|int|float $amount The amount to convert.
     * @param int $decimalPlaces The number of decimal places.
     * @return string The float value of the amount.
     */
    public function floatValue(string|int|float $amount, int $decimalPlaces): string;
}
