<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

/**
 * @api
 */
interface RegulatorServiceInterface
{
    /**
     * Forget the stored value for the given wallet.
     *
     * This method removes the stored value associated with the provided wallet from the storage.
     *
     * @param Wallet $wallet The wallet to forget.
     * @return bool True if the value was successfully forgotten, false otherwise.
     */
    public function forget(Wallet $wallet): bool;

    /**
     * Calculate the difference between the current balance and the given value.
     *
     * This method subtracts the given value from the current balance and returns the result.
     *
     * @param Wallet $wallet The wallet to calculate the difference for.
     * @return non-empty-string The difference, formatted as a string with the same decimal places as the wallet.
     */
    public function diff(Wallet $wallet): string;

    /**
     * Get the current balance of the wallet.
     *
     * This method retrieves the current balance of the wallet from the storage.
     *
     * @param Wallet $wallet The wallet to get the balance for.
     * @return non-empty-string The current balance, formatted as a string with the same decimal places as the wallet.
     */
    public function amount(Wallet $wallet): string;

    /**
     * Synchronize the stored value for the given wallet with the given value.
     *
     * This method updates the stored value associated with the provided wallet with the given value.
     * If the value does not exist, it will be created. If the value already exists, it will be updated.
     *
     * @param Wallet $wallet The wallet to synchronize.
     * @param float|int|non-empty-string $value The value to synchronize.
     * @return non-empty-string True if the synchronization was successful, false otherwise.
     */
    public function sync(Wallet $wallet, float|int|string $value): bool;

    /**
     * Increase the stored value for the given wallet by the given amount.
     *
     * This method increases the stored value associated with the provided wallet by the given amount.
     *
     * @param Wallet $wallet The wallet to increase.
     * @param float|int|non-empty-string $value The amount to increase the stored value by.
     * @return non-empty-string The updated stored value, formatted as a string with the same decimal places as the wallet.
     */
    public function increase(Wallet $wallet, float|int|string $value): string;

    /**
     * Decrease the stored value for the given wallet by the given amount.
     *
     * This method decreases the stored value associated with the provided wallet by the given amount.
     *
     * @param Wallet $wallet The wallet to decrease.
     * @param float|int|string $value The amount to decrease the stored value by.
     * @return string The updated stored value, formatted as a string with the same decimal places as the wallet.
     */
    public function decrease(Wallet $wallet, float|int|string $value): string;

    /**
     * Start committing a transaction.
     *
     * This method starts a transaction and locks the wallet for the duration of the transaction.
     */
    public function committing(): void;

    /**
     * Commit the transaction.
     *
     * This method commits the transaction and unlocks the wallet.
     */
    public function committed(): void;

    /**
     * Purge the stored values for all wallets.
     *
     * This method removes all stored values from the storage.
     */
    public function purge(): void;
}
