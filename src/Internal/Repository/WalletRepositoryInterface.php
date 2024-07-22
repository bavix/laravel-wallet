<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;

interface WalletRepositoryInterface
{
    /**
     * Create a new wallet.
     *
     * @param array{
     *     holder_type: string,
     *     holder_id: string|int,
     *     name: string,
     *     slug?: string,
     *     uuid: string,
     *     description?: string,
     *     meta: array<mixed>|null,
     *     balance?: int,
     *     decimal_places?: int,
     * } $attributes
     */
    public function create(array $attributes): Wallet;

    /**
     * Update the balances of wallets based on the provided data.
     *
     * @param non-empty-array<int, string|float|int> $data An array containing wallet IDs as keys and new balances as values.
     * @return int The number of wallets whose balances were successfully updated.
     */
    public function updateBalances(array $data): int;

    /**
     * Find a wallet by its ID.
     *
     * @param int $id The ID of the wallet to find.
     * @return Wallet|null The wallet with the given ID if found, otherwise null.
     */
    public function findById(int $id): ?Wallet;

    /**
     * Find a wallet by its UUID.
     *
     * @param string $uuid The UUID of the wallet to find.
     * @return Wallet|null The wallet with the given UUID if found, otherwise null.
     */
    public function findByUuid(string $uuid): ?Wallet;

    /**
     * Find a wallet by its holder type, holder ID, and slug.
     *
     * @param string $holderType The type of the wallet's holder.
     * @param int|string $holderId The ID of the wallet's holder.
     * @param string $slug The wallet's slug.
     * @return Wallet|null The wallet with the given holder type, holder ID, and slug if found, otherwise null.
     */
    public function findBySlug(string $holderType, int|string $holderId, string $slug): ?Wallet;

    /**
     * Find all wallets that are default wallets for the given holder type and holder IDs.
     *
     * @param string $holderType The type of the wallet's holder.
     * @param array<int|string> $holderIds An array of holder IDs.
     * @return Wallet[] An array of default wallets, indexed by their holder IDs.
     */
    public function findDefaultAll(string $holderType, array $holderIds): array;

    /**
     * Retrieve a wallet by its ID.
     *
     * @param int $id The ID of the wallet to retrieve.
     * @return Wallet The wallet with the given ID.
     *
     * @throws ModelNotFoundException If no wallet with the given ID is found.
     */
    public function getById(int $id): Wallet;

    /**
     * Retrieve a wallet by its UUID.
     *
     * @param string $uuid The UUID of the wallet to retrieve.
     * @return Wallet The wallet with the given UUID.
     *
     * @throws ModelNotFoundException If no wallet with the given UUID is found.
     */
    public function getByUuid(string $uuid): Wallet;

    /**
     * Retrieve a wallet by its slug.
     *
     * @param string $holderType The type of the wallet's holder.
     * @param int|string $holderId The ID of the wallet's holder.
     * @param string $slug The wallet's slug.
     * @return Wallet The wallet with the given holder type, holder ID, and slug.
     *
     * @throws ModelNotFoundException If no wallet with the given holder type, holder ID, and slug is found.
     */
    public function getBySlug(string $holderType, int|string $holderId, string $slug): Wallet;
}
