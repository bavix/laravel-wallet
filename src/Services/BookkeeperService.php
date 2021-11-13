<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class BookkeeperService implements BookkeeperServiceInterface
{
    private StorageServiceInterface $storage;

    private LockServiceInterface $lock;

    public function __construct(
        StorageServiceInterface $storage,
        LockServiceInterface $lock
    ) {
        $this->storage = $storage;
        $this->lock = $lock;
    }

    public function missing(Wallet $wallet): bool
    {
        return $this->storage->missing($this->getKey($wallet));
    }

    public function amount(Wallet $wallet): string
    {
        try {
            return $this->storage->get($this->getKey($wallet));
        } catch (RecordNotFoundException $recordNotFoundException) {
            $this->lock->block(
                $this->getKey($wallet),
                fn () => $this->sync($wallet, $wallet->getOriginalBalance()),
            );
        }

        return $this->storage->get($this->getKey($wallet));
    }

    public function sync(Wallet $wallet, $value): bool
    {
        return $this->storage->sync($this->getKey($wallet), $value);
    }

    public function increase(Wallet $wallet, $value): string
    {
        try {
            return $this->storage->increase($this->getKey($wallet), $value);
        } catch (RecordNotFoundException $recordNotFoundException) {
            $this->amount($wallet);
        }

        return $this->storage->increase($this->getKey($wallet), $value);
    }

    private function getKey(Wallet $wallet): string
    {
        return __CLASS__.'::'.$wallet->uuid;
    }
}
