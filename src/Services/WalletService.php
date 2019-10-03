<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\HasWallet;
use function app;

class WalletService
{

    /**
     * @param Wallet $customer
     * @param Wallet $product
     * @return int
     */
    public function discount(Wallet $customer, Wallet $product): int
    {
        if ($customer instanceof Customer && $product instanceof Discount) {
            return $product->getPersonalDiscount($customer);
        }

        // without discount
        return 0;
    }

    /**
     * @param Wallet $object
     * @return int
     */
    public function decimalPlaces(Wallet $object): int
    {
        $decimalPlaces = $this->getWallet($object)->decimal_places ?: 2;
        return 10 ** $decimalPlaces;
    }

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return int
     */
    public function fee(Wallet $wallet, int $amount): int
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = (int)($amount * $wallet->getFeePercent() / 100);
        }

        /**
         * Added minimum commission condition
         *
         * @see https://github.com/bavix/laravel-wallet/issues/64#issuecomment-514483143
         */
        if ($wallet instanceof MinimalTaxable) {
            $minimal = $wallet->getMinimalFee();
            if ($fee < $minimal) {
                $fee = $minimal;
            }
        }

        return $fee;
    }

    /**
     * The amount of checks for errors
     *
     * @param int $amount
     * @throws
     */
    public function checkAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    /**
     * @param Wallet $object
     * @param bool $autoSave
     * @return WalletModel
     */
    public function getWallet(Wallet $object, bool $autoSave = true): WalletModel
    {
        /**
         * @var WalletModel $wallet
         */
        $wallet = $object;

        if (!($object instanceof WalletModel)) {
            /**
             * @var HasWallet $object
             */
            $wallet = $object->wallet;
        }

        if ($autoSave) {
            $wallet->exists or $wallet->save();
        }

        return $wallet;
    }

    /**
     * @param Wallet $object
     * @return int
     * @deprecated use Storable::getBalance
     */
    public function getBalance(Wallet $object): int
    {
        return app(Storable::class)
            ->getBalance($object);
    }

    /**
     * @param WalletModel $wallet
     * @return bool
     */
    public function refresh(WalletModel $wallet): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, static function () use ($wallet) {
            app(Storable::class)->getBalance($wallet);
            $balance = $wallet->getAvailableBalance();
            $wallet->balance = $balance;

            return app(Storable::class)->setBalance($wallet, $balance) &&
                $wallet->save();
        });
    }

}
