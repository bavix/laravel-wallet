<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;

interface WalletRepositoryInterface
{
    public function create(array $attributes): Wallet;

    public function findById(int $id): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findBySlug(string $holderType, int $holderId, string $slug): ?Wallet;

    /** @throws ModelNotFoundException */
    public function getById(int $id): Wallet;

    /** @throws ModelNotFoundException */
    public function getByUuid(string $uuid): Wallet;

    /** @throws ModelNotFoundException */
    public function getBySlug(string $holderType, int $holderId, string $slug): Wallet;
}
