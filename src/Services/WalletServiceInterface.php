<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

interface WalletServiceInterface
{
    public function create(Model $model, array $data): Wallet;

    public function findBySlug(Model $model, string $slug): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findById(int $id): ?Wallet;

    public function getBySlug(Model $model, string $slug): Wallet;

    public function getByUuid(string $uuid): Wallet;

    public function getById(int $id): Wallet;
}
