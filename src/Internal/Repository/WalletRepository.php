<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\ModelNotFoundException as EloquentModelNotFoundException;
use Illuminate\Database\Query\Expression;

final readonly class WalletRepository implements WalletRepositoryInterface
{
    public function __construct(
        private Wallet $wallet
    ) {
    }

    public function create(array $attributes): Wallet
    {
        $instance = $this->wallet->newInstance($attributes);
        $instance->saveQuietly();

        return $instance;
    }

    /**
     * @param non-empty-array<int, string|float|int> $data
     * @param array<int, array<string, null|bool|float|int|string>> $columnsByWalletId
     */
    public function updateBalances(array $data, array $columnsByWalletId = []): int
    {
        $filteredProjectedAttributes = [];
        foreach ($columnsByWalletId as $walletId => $columns) {
            if (! array_key_exists($walletId, $data)) {
                continue;
            }

            foreach ($columns as $column => $value) {
                $filteredProjectedAttributes[$walletId][$column] = $value;
            }
        }

        // One element gives x10 speedup, on some data
        if (count($data) === 1) {
            $walletId = key($data);
            $updatePayload = [
                'balance' => current($data),
            ];

            $projected = $filteredProjectedAttributes[$walletId]
                ?? $filteredProjectedAttributes[(int) $walletId]
                ?? [];
            $updatePayload = array_merge($updatePayload, $projected);

            return $this->wallet->newQuery()
                ->whereKey($walletId)
                ->update($updatePayload);
        }

        $cases = [];
        foreach ($data as $walletId => $balance) {
            $cases[] = 'WHEN id = '.$walletId.' THEN '.$balance;
        }

        $connection = $this->wallet->getConnection();
        $pdo = $connection->getPdo();

        $updatePayload = [
            'balance' => $connection->raw('CASE '.implode(' ', $cases).' END'),
        ];

        $columns = [];
        foreach ($filteredProjectedAttributes as $attributes) {
            foreach (array_keys($attributes) as $column) {
                $columns[$column] = true;
            }
        }

        foreach (array_keys($columns) as $column) {
            $columnCases = [];
            foreach (array_keys($data) as $walletId) {
                $value = $filteredProjectedAttributes[$walletId][$column] ?? null;
                $valueSql = $value === null
                    ? 'NULL'
                    : $pdo->quote((string) $value);

                $columnCases[] = 'WHEN id = '.$walletId.' THEN '.$valueSql;
            }

            $updatePayload[$column] = new Expression('CASE '.implode(' ', $columnCases).' ELSE '.$column.' END');
        }

        return $this->wallet->newQuery()
            ->whereIn('id', array_keys($data))
            ->update($updatePayload);
    }

    public function findById(int $id): ?Wallet
    {
        try {
            return $this->getById($id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    public function findByUuid(string $uuid): ?Wallet
    {
        try {
            return $this->getByUuid($uuid);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    public function findBySlug(string $holderType, int|string $holderId, string $slug): ?Wallet
    {
        try {
            return $this->getBySlug($holderType, $holderId, $slug);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getById(int $id): Wallet
    {
        return $this->getBy([
            'id' => $id,
        ]);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): Wallet
    {
        return $this->getBy([
            'uuid' => $uuid,
        ]);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $holderType, int|string $holderId, string $slug): Wallet
    {
        return $this->getBy([
            'holder_type' => $holderType,
            'holder_id' => $holderId,
            'slug' => $slug,
        ]);
    }

    /**
     * @param array<int|string> $holderIds
     * @return Wallet[]
     */
    public function findDefaultAll(string $holderType, array $holderIds): array
    {
        return $this->wallet->newQuery()
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->where('holder_type', $holderType)
            ->whereIn('holder_id', $holderIds)
            ->get()
            ->all();
    }

    /**
     * @param non-empty-array<string, int|string> $attributes
     */
    private function getBy(array $attributes): Wallet
    {
        try {
            /** @var Wallet $wallet */
            $wallet = $this->wallet->newQuery()
                ->where($attributes)
                ->firstOrFail();

            return $wallet;
        } catch (EloquentModelNotFoundException $eloquentModelNotFoundException) {
            throw new ModelNotFoundException(
                $eloquentModelNotFoundException->getMessage(),
                ExceptionInterface::MODEL_NOT_FOUND,
                $eloquentModelNotFoundException
            );
        }
    }
}
