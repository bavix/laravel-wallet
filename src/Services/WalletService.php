<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Models\Wallet as WalletModel;

class WalletService
{
    private MathInterface $math;
    private CastService $castService;
    private LockService $lockService;
    private BookkeeperInterface $bookkeeper;

    public function __construct(
        MathInterface $math,
        CastService $castService,
        LockService $lockService,
        BookkeeperInterface $bookkeeper
    ) {
        $this->math = $math;
        $this->castService = $castService;
        $this->lockService = $lockService;
        $this->bookkeeper = $bookkeeper;
    }

    /**
     * @deprecated
     */
    public function discount(Wallet $customer, Wallet $product): int
    {
        if ($customer instanceof Customer && $product instanceof Discount) {
            return (int) $product->getPersonalDiscount($customer);
        }

        // without discount
        return 0;
    }

    /**
     * Consider the fee that the system will receive.
     *
     * @param float|int|string $amount
     */
    public function fee(Wallet $wallet, $amount): string
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = $this->math->floor(
                $this->math->div(
                    $this->math->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $this->castService->getWallet($wallet)->decimal_places
                )
            );
        }

        /**
         * Added minimum commission condition.
         *
         * @see https://github.com/bavix/laravel-wallet/issues/64#issuecomment-514483143
         */
        if ($wallet instanceof MinimalTaxable) {
            $minimal = $wallet->getMinimalFee();
            if ($this->math->compare($fee, $minimal) === -1) {
                $fee = $minimal;
            }
        }

        return (string) $fee;
    }

    /**
     * @deprecated
     * @see WalletModel::refreshBalance()
     */
    public function refresh(WalletModel $wallet): bool
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet) {
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            $wallet->balance = (string) $balance;

            return $this->bookkeeper->sync($this->castService->getWallet($wallet), $balance) &&
                (!$this->math->compare($whatIs, $balance) || $wallet->save());
        });
    }
}
