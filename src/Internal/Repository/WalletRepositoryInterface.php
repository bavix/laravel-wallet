<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;

interface WalletRepositoryInterface
{
    /**
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
     * @param non-empty-array<int, string|float|int> $data
     */
    public function updateBalances(array $data): int;

    public function findById(int $id): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findBySlug(string $holderType, int|string $holderId, string $slug): ?Wallet;

    /**
     * @param array<int|string> $holderIds
     *
     * @return Wallet[]
     */
    public function findDefaultAll(string $holderType, array $holderIds): array;

    /**
     * @throws ModelNotFoundException
     */
    public function getById(int $id): Wallet;

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): Wallet;

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $holderType, int|string $holderId, string $slug): Wallet;
}
