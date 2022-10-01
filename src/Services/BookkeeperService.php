<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Models\Wallet;

/**
 * @internal
 */
final class BookkeeperService implements BookkeeperServiceInterface
{
    public function __construct(
        private StorageServiceInterface $storageService,
        private LockServiceInterface $lockService
    ) {
    }

    public function missing(Wallet $wallet): bool
    {
        return $this->storageService->missing($wallet->uuid);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function amount(Wallet $wallet): string
    {
        try {
            return $this->storageService->get($wallet->uuid);
        } catch (RecordNotFoundException) {
            $this->lockService->block(
                $wallet->uuid,
                fn () => $this->sync($wallet, $wallet->getOriginalBalanceAttribute()),
            );
        }

        return $this->storageService->get($wallet->uuid);
    }

    public function sync(Wallet $wallet, float|int|string $value): bool
    {
        return $this->storageService->sync($wallet->uuid, $value);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(Wallet $wallet, float|int|string $value): string
    {
        try {
            return $this->storageService->increase($wallet->uuid, $value);
        } catch (RecordNotFoundException) {
            $this->amount($wallet);
        }

        return $this->storageService->increase($wallet->uuid, $value);
    }

    /**
     * @template T of non-empty-array<string, Wallet>
     *
     * @param T $wallets
     *
     * @return non-empty-array<key-of<T>, string>
     */
    public function multiAmount(array $wallets): array
    {
        $results = [];
        foreach ($wallets as $uuid => $wallet) {
            $results[$uuid] = $this->amount($wallet);
        }

        return $results;
    }

    public function multiSync(array $balances): bool
    {
        foreach ($balances as $uuid => $balance) {
            $this->storageService->sync($uuid, $balance);
        }

        return true;
    }

    public function multiIncrease(array $wallets, array $incrementValues): array
    {
        $result = [];
        foreach ($incrementValues as $uuid => $incrementValue) {
            $result[$uuid] = $this->increase($wallets[$uuid], $incrementValue);
        }

        return $result;
    }
}
