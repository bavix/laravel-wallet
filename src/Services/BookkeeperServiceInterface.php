<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Models\Wallet;

/**
 * @api
 */
interface BookkeeperServiceInterface
{
    /**
     * Forgets the given wallet.
     *
     * This method removes any data related to the specified wallet.
     * It is used to remove the wallet from the bookkeeper's records.
     *
     * @param Wallet $wallet The wallet to forget.
     * @return bool True if the wallet was successfully forgotten, false otherwise.
     *
     * @throws RecordNotFoundException If the wallet does not exist in the database.
     */
    public function forget(Wallet $wallet): bool;

    /**
     * @throws RecordNotFoundException If the wallet does not exist in the database.
     */
    public function amount(Wallet $wallet): string;

    /**
     * Synchronizes the balance of the given wallet with the specified value.
     *
     * This method updates the balance of the wallet by setting it to the specified value.
     * If the specified value is greater than the current balance, the balance will be increased.
     * If the specified value is less than the current balance, the balance will be decreased.
     * If the specified value is equal to the current balance, the balance will remain unchanged.
     *
     * @param Wallet $wallet The wallet whose balance needs to be synchronized.
     * @param float|int|string $value The new balance value for the wallet.
     *
     * @throws RecordNotFoundException If the wallet does not exist.
     */
    public function sync(Wallet $wallet, float|int|string $value): bool;

    /**
     * Increases the balance of a wallet by the given value.
     *
     * This method updates the balance of the specified wallet by adding the given value.
     * If the given value is positive, the balance will be increased.
     * If the given value is negative, the balance will be decreased.
     * If the given value is zero, the balance will remain unchanged.
     *
     * @param Wallet $wallet The wallet to increase the balance for.
     * @param float|int|string $value The value to increase the balance by.
     * @return string The new balance as a string.
     *
     * @throws RecordNotFoundException If the wallet does not exist.
     */
    public function increase(Wallet $wallet, float|int|string $value): string;

    /**
     * Retrieves the balance amounts for multiple wallets.
     *
     * This method takes an array of wallets and returns an associative array
     * with the wallet UUIDs as keys and their balance amounts as values.
     *
     * @template T of non-empty-array<string, Wallet>
     *
     * @param T $wallets An array of wallets.
     * @return non-empty-array<key-of<T>, string> An associative array with the wallet UUIDs as keys
     *                                             and their balance amounts as values.
     */
    public function multiAmount(array $wallets): array;

    /**
     * Synchronizes multiple wallet balances with the proposed values.
     *
     * This comprehensive method takes an associative array with wallet UUIDs as keys and their new balance values as values.
     * It aims to align each wallet's current balance with its corresponding new value provided in the array. The process
     * determines whether to increase, decrease, or maintain the current balance based on a comparison between the current
     * and the given balance values.
     *
     * Operations:
     * - If the proposed balance is higher than the existing one, the wallet's balance is increased accordingly.
     * - If the proposed balance is lower, the wallet's balance is decreased to match the new value.
     * - If the proposed balance matches the current one, no changes are made to the wallet's balance.
     *
     * The method ensures that all specified wallets undergo the synchronization process, adhering to the given values,
     * and returns a boolean indicating the overall success of the operation.
     *
     * @param non-empty-array<string, float|int|string> $balances An associative array mapping wallet UUIDs to their new balance values.
     *                                                             Each entry specifies the target balance for a wallet identified by its UUID.
     * @return bool True if all specified wallets were successfully synchronized with the given balances, false otherwise.
     *
     * @throws RecordNotFoundException Thrown if any of the specified wallets cannot be found in the system. This ensures
     *                                 that only existing wallets are processed, safeguarding against synchronization
     *                                 attempts on non-existent wallets.
     *
     * @see Wallet The Wallet entity that represents the individual wallets to be synchronized.
     * @see Wallet::uuid The property within the Wallet class that uniquely identifies each wallet.
     */
    public function multiSync(array $balances): bool;

    /**
     * Increases multiple wallet balances by the given values.
     *
     * This method takes an associative array of wallets and an associative array of increment values.
     * The keys of the first array are wallet UUIDs and the values are the wallet objects.
     * The keys of the second array are wallet UUIDs and the values are the increment values.
     *
     * The method updates the balance of each wallet by the corresponding given value.
     * The balance of each wallet will be increased by the corresponding value.
     *
     * @template T of non-empty-array<string, float|int|string>
     *
     * @param non-empty-array<key-of<T>, Wallet> $wallets An associative array of wallets.
     *                                                    The keys are wallet UUIDs and the values are the wallet objects.
     * @param T $incrementValues An associative array of increment values.
     *                           The keys are wallet UUIDs and the values are the increment values.
     * @return non-empty-array<key-of<T>, string> An associative array with the new balance amounts of the wallets.
     *                                           The keys are the wallet UUIDs and the values are the new balance amounts as strings.
     *
     * @throws RecordNotFoundException If any of the wallets do not exist.
     */
    public function multiIncrease(array $wallets, array $incrementValues): array;
}
