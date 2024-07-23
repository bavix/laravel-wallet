<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface StateServiceInterface
{
    /**
     * Execute a transaction that will fork the state of multiple wallets.
     *
     * This method is used to ensure that the state of multiple wallets is consistent across a series of operations.
     * When this method is called, it creates a new transaction that locks the wallets and executes the provided
     * callback function. The callback function should perform a series of operations on the wallets, and return
     * an associative array where the keys are the UUIDs of the wallets and the values are the new state of the wallets.
     *
     * The state of each wallet is validated against the state of the wallet at the beginning of the transaction.
     * If any of the wallets have been modified since the transaction began, a `WalletStateConsistencyException`
     * exception will be thrown.
     *
     * @param non-empty-string[] $uuids The UUIDs of the wallets to be forked.
     * @param callable(): array<non-empty-string, non-empty-string> $value A callback function that performs a series of operations on the
     *                                                  wallets and returns an associative array of the new state of the wallets.
     *                                                  The keys of the array should be the UUIDs of the wallets, and the values
     *                                                  should be the new state of the wallets.
     */
    public function multiFork(array $uuids, callable $value): void;

    /**
     * Get the state of a wallet.
     *
     * @param non-empty-string $uuid The UUID of the wallet to retrieve the state of.
     * @return non-empty-string|null The state of the wallet, or null if the wallet does not exist.
     */
    public function get(string $uuid): ?string;

    /**
     * Delete the state of a wallet.
     *
     * This method is used to remove the state of a wallet from the storage.
     *
     * @param non-empty-string $uuid The UUID of the wallet to delete the state of.
     */
    public function drop(string $uuid): void;
}
