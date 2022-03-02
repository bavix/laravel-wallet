<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Models\Wallet;

interface WalletRepositoryInterface
{
    public function create(array $attributes): Wallet;

    public function findById(int $id): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findBySlug(string $holderType, int|string $holderId, string $slug): ?Wallet;

    public function getById(int $id): Wallet;

    public function getByUuid(string $uuid): Wallet;

    public function getBySlug(string $holderType, int|string $holderId, string $slug): Wallet;
}
