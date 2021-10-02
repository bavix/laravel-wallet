<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\HasWallet;
use Throwable;

class WalletService
{
    private ConsistencyInterface $consistency;
    private DbService $dbService;
    private MathInterface $math;
    private LockService $lockService;
    private Storable $store;

    public function __construct(
        DbService $dbService,
        MathInterface $math,
        LockService $lockService,
        Storable $store,
        ConsistencyInterface $consistency
    ) {
        $this->dbService = $dbService;
        $this->math = $math;
        $this->lockService = $lockService;
        $this->store = $store;
        $this->consistency = $consistency;
    }

    public function discount(Wallet $customer, Wallet $product): int
    {
        if ($customer instanceof Customer && $product instanceof Discount) {
            return (int) $product->getPersonalDiscount($customer);
        }

        // without discount
        return 0;
    }

    public function decimalPlacesValue(Wallet $object): int
    {
        return $this->getWallet($object)->decimal_places ?: 2;
    }

    public function decimalPlaces(Wallet $object): string
    {
        return $this->math->pow(10, $this->decimalPlacesValue($object));
    }

    /**
     * Consider the fee that the system will receive.
     *
     * @param int|string $amount
     *
     * @return float|int
     */
    public function fee(Wallet $wallet, $amount)
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $placesValue = $this->decimalPlacesValue($wallet);
            $fee = $this->math->floor(
                $this->math->div(
                    $this->math->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $placesValue
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

    /**
     * The amount of checks for errors.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     *
     * @deprecated
     * @see ConsistencyInterface::checkPositive()
     */
    public function checkAmount($amount): void
    {
        $this->consistency->checkPositive($amount);
    }

    public function getWallet(Wallet $object, bool $autoSave = true): WalletModel
    {
        /** @var WalletModel $wallet */
        $wallet = $object;

        if (!($object instanceof WalletModel)) {
            /** @var HasWallet $object */
            $wallet = $object->wallet;
        }

        if ($autoSave) {
            $wallet->exists or $wallet->save();
        }

        return $wallet;
    }

    public function refresh(WalletModel $wallet): bool
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet) {
            $this->store->getBalance($wallet);
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            $wallet->balance = $balance;

            return $this->store->setBalance($wallet, $balance) &&
                (!$this->math->compare($whatIs, $balance) || $wallet->save());
        });
    }

    /**
     * @throws Throwable
     */
    public function adjustment(WalletModel $wallet, ?array $meta = null): void
    {
        $this->dbService->transaction(function () use ($wallet, $meta) {
            $this->store->getBalance($wallet);
            $adjustmentBalance = $wallet->balance;
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
