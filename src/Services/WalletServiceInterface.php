<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @api
 */
interface WalletServiceInterface
{
    /**
     * Create a new wallet for the given model.
     *
     * @param Model $model The model the wallet belongs to.
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<mixed>|null,
     *     decimal_places?: positive-int,
     * } $data The data for the new wallet.
     * @return Wallet The newly created wallet.
     */
    public function create(Model $model, array $data): Wallet;

    /**
     * Find a wallet by slug for the given model.
     *
     * @param Model $model The model the wallet belongs to.
     * @param string $slug The slug of the wallet.
     * @return Wallet|null The wallet with the given slug if found, otherwise null.
     */
    public function findBySlug(Model $model, string $slug): ?Wallet;

    /**
     * Find a wallet by UUID.
     *
     * @param string $uuid The UUID of the wallet.
     * @return Wallet|null The wallet with the given UUID if found, otherwise null.
     */
    public function findByUuid(string $uuid): ?Wallet;

    /**
     * Find a wallet by ID.
     *
     * @param int $id The ID of the wallet.
     * @return Wallet|null The wallet with the given ID if found, otherwise null.
     */
    public function findById(int $id): ?Wallet;

    /**
     * Get a wallet by slug for the given model.
     *
     * @param Model $model The model the wallet belongs to.
     * @param string $slug The slug of the wallet.
     * @return Wallet The wallet with the given slug.
     *
     * @throws ModelNotFoundException If the wallet with the given slug is not found.
     */
    public function getBySlug(Model $model, string $slug): Wallet;

    /**
     * Get a wallet by UUID.
     *
     * @param string $uuid The UUID of the wallet.
     * @return Wallet The wallet with the given UUID.
     *
     * @throws ModelNotFoundException If the wallet with the given UUID is not found.
     */
    public function getByUuid(string $uuid): Wallet;

    /**
     * Get a wallet by its ID.
     *
     * @param int $id The ID of the wallet.
     * @return Wallet The wallet with the given ID.
     *
     * @throws ModelNotFoundException If the wallet with the given ID is not found.
     */
    public function getById(int $id): Wallet;
}
