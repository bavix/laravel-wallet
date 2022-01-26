<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;

interface WalletRepositoryInterface
{
    public function create(array $attributes): Wallet;

    public function findById(int|string $id): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findBySlug(string $holderType, int|string $holderId, string $slug): ?Wallet;

    /** @throws ModelNotFoundException */
    public function getById(int|string $id): Wallet;

    /** @throws ModelNotFoundException */
    public function getByUuid(string $uuid): Wallet;

    /** @throws ModelNotFoundException */
    public function getBySlug(string $holderType, int|string $holderId, string $slug): Wallet;
}
