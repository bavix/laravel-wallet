<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;

final class WalletServiceLegacy
{
    private MathServiceInterface $math;
    private BookkeeperServiceInterface $bookkeeper;
    private AtomicServiceInterface $atomicService;

    public function __construct(
        MathServiceInterface $math,
        BookkeeperServiceInterface $bookkeeper,
        AtomicServiceInterface $atomicService
    ) {
        $this->math = $math;
        $this->bookkeeper = $bookkeeper;
        $this->atomicService = $atomicService;
    }

    /**
     * @deprecated
     * @see WalletModel::refreshBalance()
     */
    public function refresh(WalletModel $wallet): bool
    {
        return $this->atomicService->block($wallet, function () use ($wallet) {
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
