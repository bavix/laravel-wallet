<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;

/**
 * @api
 */
interface ConsistencyServiceInterface
{
    /**
     * Checks if the given amount is positive.
     *
     * This method throws an AmountInvalid exception if the given amount is not positive.
     *
     * @param float|int|string $amount The amount to check.
     *
     * @throws AmountInvalid If the given amount is not positive.
     */
    public function checkPositive(float|int|string $amount): void;

    /**
     * Checks if the given amount is within the wallet's balance.
     *
     * This method throws a BalanceIsEmpty exception if the wallet's balance is empty.
     * It throws an InsufficientFunds exception if the amount exceeds the wallet's balance.
     *
     * @param Wallet $object The wallet to check.
     * @param float|int|string $amount The amount to check.
     * @param bool $allowZero Whether to allow zero amounts. Defaults to false.
     *
     * @throws BalanceIsEmpty If the wallet's balance is empty.
     * @throws InsufficientFunds If the amount exceeds the wallet's balance.
     */
    public function checkPotential(Wallet $object, float|int|string $amount, bool $allowZero = false): void;

    /**
     * Checks if the given balance can be safely withdrawn by the specified amount.
     *
     * This method returns true if the balance can be withdrawn, and false otherwise.
     *
     * @param float|int|string $balance The balance to check.
     * @param float|int|string $amount The amount to withdraw.
     * @param bool $allowZero Whether to allow zero amounts. Defaults to false.
     * @return bool Returns true if the balance can be withdrawn, false otherwise.
     *
     * @throws AmountInvalid If the given balance or amount is not positive.
     */
    public function canWithdraw(float|int|string $balance, float|int|string $amount, bool $allowZero = false): bool;

    /**
     * Checks the consistency of multiple transfers between wallets.
     *
     * This method ensures that the transfer of the specified amount from each wallet can be performed without
     * exceeding the wallet's balance. It throws a BalanceIsEmpty exception if any wallet's balance is empty.
     * It throws an InsufficientFunds exception if the total amount of the transfers exceeds any wallet's balance.
     *
     * @param TransferLazyDtoInterface[] $objects An array of transfer objects.
     *
     * @throws BalanceIsEmpty If any wallet's balance is empty.
     * @throws InsufficientFunds If the total amount of the transfers exceeds any wallet's balance.
     */
    public function checkTransfer(array $objects): void;
}
