<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use function app;
use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Throwable;

class WalletService
{
    private MathInterface $mathService;

    public function __construct(MathInterface $mathService)
    {
        $this->mathService = $mathService;
    }

    public function discount(Wallet $customer, Wallet $product): int
    {
        return app(DiscountService::class)->discount($customer, $product);
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
            $placesValue = app(FloatService::class)->exponent($wallet);
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $placesValue
                )
            );
        }

        /*
         * Added minimum commission condition.
         *
         * @see https://github.com/bavix/laravel-wallet/issues/64#issuecomment-514483143
         */
        if ($wallet instanceof MinimalTaxable) {
            $minimal = $wallet->getMinimalFee();
            if ($this->mathService->compare($fee, $minimal) === -1) {
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
     */
    public function checkAmount($amount): void
    {
        if ($this->mathService->compare($amount, 0) === -1) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    public function getWallet(Wallet $object, bool $autoSave = true): WalletModel
    {
        $wallet = app(CastService::class)->getWalletModel($object);

        if ($autoSave) {
            $wallet->exists or $wallet->save();
        }

        return $wallet;
    }

    public function refresh(WalletModel $wallet): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($wallet) {
            app(Storable::class)->getBalance($wallet);
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            $wallet->balance = $balance;

            return app(Storable::class)->setBalance($wallet, $balance) &&
                (!$this->mathService->compare($whatIs, $balance) || $wallet->save());
        });
    }

    /**
     * @throws Throwable
     */
    public function adjustment(WalletModel $wallet, ?array $meta = null): void
    {
        app(DbService::class)->transaction(function () use ($wallet, $meta) {
            app(Storable::class)->getBalance($wallet);
            $adjustmentBalance = $wallet->balance;
            $wallet->refreshBalance();
            $difference = $this->mathService->sub($wallet->balance, $adjustmentBalance);

            switch ($this->mathService->compare($difference, 0)) {
                case -1:
                    $wallet->deposit($this->mathService->abs($difference), $meta);

                    break;
                case 1:
                    $wallet->forceWithdraw($this->mathService->abs($difference), $meta);

                    break;
            }
        });
    }
}
