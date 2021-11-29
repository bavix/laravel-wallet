<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class RegulatorService implements RegulatorServiceInterface
{
    private BookkeeperServiceInterface $bookkeeperService;
    private StorageServiceInterface $storageService;
    private MathServiceInterface $mathService;
    private string $idempotentKey;

    /** @var Wallet[] */
    private array $wallets = [];

    public function __construct(
        UuidFactoryServiceInterface $uuidFactoryService,
        BookkeeperServiceInterface $bookkeeperService,
        StorageServiceInterface $storageService,
        MathServiceInterface $mathService
    ) {
        $this->idempotentKey = $uuidFactoryService->uuid4();
        $this->bookkeeperService = $bookkeeperService;
        $this->storageService = $storageService;
        $this->mathService = $mathService;
    }

    public function missing(Wallet $wallet): bool
    {
        unset($this->wallets[$wallet->uuid]);

        return $this->storageService->missing($this->getKey($wallet->uuid));
    }

    public function diff(Wallet $wallet): string
    {
        try {
            return $this->mathService->round($this->storageService->get($this->getKey($wallet->uuid)));
        } catch (RecordNotFoundException $exception) {
            return '0';
        }
    }

    public function amount(Wallet $wallet): string
    {
        return $this->mathService->round(
            $this->mathService->add($this->bookkeeperService->amount($wallet), $this->diff($wallet))
        );
    }

    /** @param float|int|string $value */
    public function sync(Wallet $wallet, $value): bool
    {
        $this->persist($wallet);

        return $this->storageService->sync(
            $this->getKey($wallet->uuid),
            $this->mathService->round(
                $this->mathService->negative($this->mathService->sub($this->amount($wallet), $value))
            )
        );
    }

    /** @param float|int|string $value */
    public function increase(Wallet $wallet, $value): string
    {
        $this->persist($wallet);

        try {
            $this->storageService->increase($this->getKey($wallet->uuid), $value);
        } catch (RecordNotFoundException $exception) {
            $value = $this->mathService->round($value);
            $this->storageService->sync($this->getKey($wallet->uuid), $value);
        }

        return $this->amount($wallet);
    }

    /** @param float|int|string $value */
    public function decrease(Wallet $wallet, $value): string
    {
        return $this->increase($wallet, $this->mathService->negative($value));
    }

    public function approve(): void
    {
        foreach ($this->wallets as $wallet) {
            $diffValue = $this->diff($wallet);
            if ($this->mathService->compare($diffValue, 0) === 0) {
                continue;
            }

            $balance = $this->bookkeeperService->increase($wallet, $diffValue);
            $wallet->newQuery()->whereKey($wallet->getKey())->update(['balance' => $balance]); // ?qN
            $wallet->fill(['balance' => $balance])->syncOriginalAttribute('balance');
        }

        $this->purge();
    }

    public function purge(): void
    {
        foreach ($this->wallets as $wallet) {
            $this->missing($wallet);
        }
    }

    private function persist(Wallet $wallet): void
    {
        $this->wallets[$wallet->uuid] = $wallet;
    }

    private function getKey(string $uuid): string
    {
        return $this->idempotentKey.'::'.$uuid;
    }
}
