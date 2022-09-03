<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\ModelNotFoundException as EloquentModelNotFoundException;

final class WalletRepository implements WalletRepositoryInterface
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
     *
     * @return Wallet[]
     */
    public function findDefaultAll(string $holderType, array $holderIds): array
    {
        return $this->wallet->newQuery()
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->where('holder_type', $holderType)
            ->whereIn('holder_id', $holderIds)
            ->get()
            ->all()
        ;
    }

    /**
     * @param array<string, int|string> $attributes
     */
    private function getBy(array $attributes): Wallet
    {
        try {
            $wallet = $this->wallet->newQuery()
                ->where($attributes)
                ->firstOrFail()
            ;
            assert($wallet instanceof Wallet);

            return $wallet;
        } catch (EloquentModelNotFoundException $exception) {
            throw new ModelNotFoundException(
                $exception->getMessage(),
                ExceptionInterface::MODEL_NOT_FOUND,
                $exception
            );
        }
    }
}
