<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Brick\Math\Exception\DivisionByZeroException;

interface MathServiceInterface
{
    /**
     * Add two numbers.
     *
     * This method adds two numbers and returns the result as a string.
     *
     * @param float|int|string $first The first number to add.
     * @param float|int|string $second The second number to add.
     * @param int|null $scale The scale to use for rounding. Defaults to null, which means the scale will be determined automatically.
     * @return string The sum of the two numbers.
     */
    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * Subtract two numbers.
     *
     * This method subtracts the second number from the first number and returns the result as a string.
     *
     * @param float|int|string $first The first number to subtract from.
     * @param float|int|string $second The number to subtract.
     * @param int|null $scale The scale to use for rounding. Defaults to null, which means the scale will be determined automatically.
     * @return string The difference between the two numbers.
     */
    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * Divide two numbers.
     *
     * This method divides the first number by the second number and returns the result as a string.
     *
     * @param float|int|string $first The first number to divide.
     * @param float|int|string $second The number to divide by.
     * @param int|null $scale The scale to use for rounding. Defaults to null, which means the scale will be determined automatically.
     * @return string The result of the division.
     *
     * @throws DivisionByZeroException If the second number is zero.
     */
    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * Multiply two numbers.
     *
     * This method multiplies the first number by the second number and returns the result as a string.
     * The result can be scaled to a specific number of decimal places as specified by the $scale parameter.
     * If $scale is not provided, a default scale (defined elsewhere in the implementation) will be used.
     *
     * @param float|int|string $first The first number to multiply. This can be a float, int, or a numeric string.
     * @param float|int|string $second The second number to multiply. Similar to $first, it accepts float, int, or numeric string.
     * @param int|null $scale Optional. The scale to use for rounding the result. Defaults to null, indicating automatic scale determination.
     * @return string The product of the two numbers, represented as a string.
     */
    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * Raise a number to the power of another number.
     *
     * This method calculates the result of raising the first number to the power of the second number and returns the result as a string.
     * The result can be scaled to a specific number of decimal places as specified by the $scale parameter.
     * If $scale is not provided, a default scale (defined elsewhere in the implementation) will be used.
     *
     * @param float|int|string $first The base number to raise.
     * @param float|int|string $second The exponent to raise the base number to.
     * @param int|null $scale Optional. The scale to use for rounding the result. Defaults to null, indicating automatic scale determination.
     * @return string The result of the exponentiation, represented as a string.
     */
    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    /**
     * Raise the number 10 to the power of another number.
     *
     * This method calculates the result of raising the number 10 to the power of the given number and returns
     * the result as a string.
     *
     * @param float|int|string $number The exponent to raise the number 10 to.
     * @return string The result of the exponentiation, represented as a string.
     */
    public function powTen(float|int|string $number): string;

    /**
     * Round a number to a specified precision.
     *
     * This method provides a way to round numerical values (whether they are floats, integers, or numeric strings)
     * to a specified level of precision. The precision is defined by the number of decimal places to round to.
     * The rounding follows the standard mathematical rules for rounding.
     *
     * @param float|int|string $number The number to be rounded. Can be of type float, int, or a numeric string.
     * @param int $precision The number of decimal places to round to. Defaults to 0, meaning rounding to the nearest whole number.
     * @return string The rounded number, represented as a string. This ensures consistent precision and format, especially useful in financial calculations.
     */
    public function round(float|int|string $number, int $precision = 0): string;

    /**
     * Get the floor value of a number.
     *
     * This method returns the largest integer less than or equal to the specified number.
     *
     * @param float|int|string $number The number to get the floor value for.
     * @return string The floor value of the number represented as a string.
     */
    public function floor(float|int|string $number): string;

    /**
     * Get the ceiling value of a number.
     *
     * This method returns the smallest integer greater than or equal to the specified number.
     *
     * @param float|int|string $number The number to get the ceiling value for.
     * @return string The ceiling value of the number represented as a string.
     */
    public function ceil(float|int|string $number): string;

    /**
     * Get the absolute value of a number.
     *
     * The absolute value of a number is the value without considering whether it is positive or negative.
     *
     * @param float|int|string $number The number for which to get the absolute value.
     * @return string The absolute value of the number represented as a string.
     */
    public function abs(float|int|string $number): string;

    /**
     * Get the negative value of a number.
     *
     * The negative value of a number is the same as the number multiplied by -1.
     *
     * @param float|int|string $number The number for which to get the negative value.
     * @return string The negative value of the number represented as a string.
     */
    public function negative(float|int|string $number): string;

    /**
     * Compare two numbers.
     *
     * This method compares two numbers and returns an integer value indicating their relationship.
     *
     * @param float|int|string $first The first number to compare.
     * @param float|int|string $second The second number to compare.
     * @return int Returns an integer less than, equal to, or greater than zero if the first number is considered
     *             to be respectively less than, equal to, or greater than the second.
     */
    public function compare(float|int|string $first, float|int|string $second): int;
}
