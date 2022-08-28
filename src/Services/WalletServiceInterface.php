<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

interface WalletServiceInterface
{
    /**
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<mixed>|null,
     *     decimal_places?: positive-int,
     * } $data
     */
    public function create(Model $model, array $data): Wallet;

    public function findBySlug(Model $model, string $slug): ?Wallet;

    public function findByUuid(string $uuid): ?Wallet;

    public function findById(int $id): ?Wallet;

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(Model $model, string $slug): Wallet;

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): Wallet;

    /**
     * @throws ModelNotFoundException
     */
    public function getById(int $id): Wallet;
}
