<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;

final class WalletServiceLegacy
{
    private MathServiceInterface $math;
    private LockServiceInterface $lockService;
    private BookkeeperServiceInterface $bookkeeper;
    private AtomicKeyServiceInterface $atomicKeyService;

    public function __construct(
        MathServiceInterface $math,
        LockServiceInterface $lockService,
        BookkeeperServiceInterface $bookkeeper,
        AtomicKeyServiceInterface $atomicKeyService
    ) {
        $this->math = $math;
        $this->lockService = $lockService;
        $this->bookkeeper = $bookkeeper;
        $this->atomicKeyService = $atomicKeyService;
    }

    /**
     * @deprecated
     * @see WalletModel::refreshBalance()
     */
    public function refresh(WalletModel $wallet): bool
    {
        return $this->lockService->block($this->atomicKeyService->getIdentifier($wallet), function () use ($wallet) {
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            if ($this->math->compare($whatIs, $balance) === 0) {
                return true;
            }

            $wallet->balance = (string) $balance;

            return $wallet->save() && $this->bookkeeper->sync($wallet, $balance);
        });
    }
}
