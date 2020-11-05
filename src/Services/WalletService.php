<?php

namespace Bavix\Wallet\Services;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\HasWallet;

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
            return (int) $product->getPersonalDiscount($customer);
        }

        // without discount
        return 0;
    }

    /**
     * @param Wallet $object
     * @return int
     */
    public function decimalPlacesValue(Wallet $object): int
    {
        return $this->getWallet($object)->decimal_places ?: 2;
    }

    /**
     * @param Wallet $object
     * @return string
     */
    public function decimalPlaces(Wallet $object): string
    {
        return app(Mathable::class)
            ->pow(10, $this->decimalPlacesValue($object));
    }

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int|string $amount
     * @return float|int
     */
    public function fee(Wallet $wallet, $amount)
    {
        $fee = 0;
        $math = app(Mathable::class);
        if ($wallet instanceof Taxable) {
            $placesValue = $this->decimalPlacesValue($wallet);
            $fee = $math->floor(
                $math->div(
                    $math->mul($amount, $wallet->getFeePercent(), 0),
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
            if (app(Mathable::class)->compare($fee, $minimal) === -1) {
                $fee = $minimal;
            }
        }

        return $fee;
    }

    /**
     * The amount of checks for errors.
     *
     * @param int|string $amount
     * @throws
     */
    public function checkAmount($amount): void
    {
        if (app(Mathable::class)->compare($amount, 0) === -1) {
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

        if (! ($object instanceof WalletModel)) {
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
     * @param WalletModel $wallet
     * @return bool
     */
    public function refresh(WalletModel $wallet): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, static function () use ($wallet) {
            $math = app(Mathable::class);
            app(Storable::class)->getBalance($wallet);
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            $wallet->balance = $balance;

            return app(Storable::class)->setBalance($wallet, $balance) &&
                (! $math->compare($whatIs, $balance) || $wallet->save());
        });
    }

    /**
     * @param WalletModel $wallet
     * @param array|null $meta
     * @return void
     * @throws
     */
    public function adjustment(WalletModel $wallet, ?array $meta = null): void
    {
        app(DbService::class)->transaction(function () use ($wallet, $meta) {
            $math = app(Mathable::class);
            app(Storable::class)->getBalance($wallet);
            $adjustmentBalance = $wallet->balance;
            $wallet->refreshBalance();
            $difference = $math->sub($wallet->balance, $adjustmentBalance);

            switch ($math->compare($difference, 0)) {
                case -1:
                    $wallet->deposit($math->abs($difference), $meta);
                    break;
                case 1:
                    $wallet->forceWithdraw($math->abs($difference), $meta);
                    break;
            }
        });
    }
}
