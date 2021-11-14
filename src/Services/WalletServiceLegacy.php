<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;

final class WalletServiceLegacy
{
    private MathServiceInterface $math;
    private LockServiceLegacy $lockService;
    private BookkeeperServiceInterface $bookkeeper;

    public function __construct(
        MathServiceInterface $math,
        LockServiceLegacy $lockService,
        BookkeeperServiceInterface $bookkeeper
    ) {
        $this->math = $math;
        $this->lockService = $lockService;
        $this->bookkeeper = $bookkeeper;
    }

    /**
     * @deprecated
     * @see WalletModel::refreshBalance()
     */
    public function refresh(WalletModel $wallet): bool
    {
        return $this->lockService->lock($wallet, function () use ($wallet) {
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
