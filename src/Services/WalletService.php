<?php

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
use Throwable;

class WalletService
{
    private DbService $dbService;
    private MathInterface $math;
    private LockService $lockService;
    private BookkeeperInterface $bookkeeper;

    public function __construct(
        DbService $dbService,
        MathInterface $math,
        LockService $lockService,
        BookkeeperInterface $bookkeeper
    ) {
        $this->dbService = $dbService;
        $this->math = $math;
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
     * @param int|string $amount
     *
     * @return float|int|string
     */
    public function fee(Wallet $wallet, $amount)
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = $this->math->floor(
                $this->math->div(
                    $this->math->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $this->getWallet($wallet)->decimal_places
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

        return $fee;
    }

    public function getWallet(Wallet $object, bool $autoSave = true): WalletModel
    {
        return app(CastService::class)->getWallet($object, $autoSave);
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

            return $this->bookkeeper->sync($this->getWallet($wallet), $balance) &&
                (!$this->math->compare($whatIs, $balance) || $wallet->save());
        });
    }

    /**
     * @throws Throwable
     *
     * @deprecated
     * @see WalletModel::adjustmentBalance()
     */
    public function adjustment(WalletModel $wallet, ?array $meta = null): void
    {
        $this->dbService->transaction(function () use ($wallet, $meta) {
            $walletObject = $this->getWallet($wallet);
            $adjustmentBalance = $this->bookkeeper->amount($walletObject);
            $wallet->refreshBalance();
            $difference = $this->math->sub($wallet->balance, $adjustmentBalance);

            switch ($this->math->compare($difference, 0)) {
                case -1:
                    $wallet->deposit($this->math->abs($difference), $meta);

                    break;
                case 1:
                    $wallet->forceWithdraw($this->math->abs($difference), $meta);

                    break;
            }
        });
    }
}
