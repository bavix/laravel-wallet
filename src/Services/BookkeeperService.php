<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class BookkeeperService implements BookkeeperServiceInterface
{
    private StorageServiceInterface $storageService;
    private LockServiceInterface $lockService;

    public function __construct(
        StorageServiceInterface $storageService,
        LockServiceInterface $lockService
    ) {
        $this->storageService = $storageService;
        $this->lockService = $lockService;
    }

    public function missing(Wallet $wallet): bool
    {
        return $this->storageService->missing($this->getKey($wallet));
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function amount(Wallet $wallet): string
    {
        try {
            return $this->storageService->get($this->getKey($wallet));
        } catch (RecordNotFoundException $recordNotFoundException) {
            $this->lockService->block(
                $this->getKey($wallet),
                fn () => $this->sync($wallet, $wallet->getOriginalBalanceAttribute()),
            );
        }

        return $this->storageService->get($this->getKey($wallet));
    }

    public function sync(Wallet $wallet, $value): bool
    {
        return $this->storageService->sync($this->getKey($wallet), $value);
    }

    /**
     * @param float|int|string $value
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(Wallet $wallet, $value): string
    {
        try {
            return $this->storageService->increase($this->getKey($wallet), $value);
        } catch (RecordNotFoundException $recordNotFoundException) {
            $this->amount($wallet);
        }

        return $this->storageService->increase($this->getKey($wallet), $value);
    }

    private function getKey(Wallet $wallet): string
    {
        return __CLASS__.'::'.$wallet->uuid;
    }
}
