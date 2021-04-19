<?php

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\WalletService;
use function config;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Trait HasWallet.
 *
 *
 * @property-read Collection|WalletModel[] $wallets
 * @property-read int $balance
 */
trait HasWallet
{
    use MorphOneWallet;

    /**
     * The input means in the system.
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function deposit(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $self */
        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $amount, $meta, $confirmed) {
            return app(CommonService::class)
                ->deposit($self, $amount, $meta, $confirmed);
        });
    }

    /**
     * Magic laravel framework method, makes it
     *  possible to call property balance.
     *
     * Example:
     *  $user1 = User::first()->load('wallet');
     *  $user2 = User::first()->load('wallet');
     *
     * Without static:
     *  var_dump($user1->balance, $user2->balance); // 100 100
     *  $user1->deposit(100);
     *  $user2->deposit(100);
     *  var_dump($user1->balance, $user2->balance); // 200 200
     *
     * With static:
     *  var_dump($user1->balance, $user2->balance); // 100 100
     *  $user1->deposit(100);
     *  var_dump($user1->balance); // 200
     *  $user2->deposit(100);
     *  var_dump($user2->balance); // 300
     *
     * @throws Throwable
     */
    public function getBalanceAttribute(): string
    {
        /** @var Wallet $this */
        return app(Storable::class)->getBalance($this);
    }

    /**
     * all user actions on wallets will be in this method.
     */
    public function transactions(): MorphMany
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model', Transaction::class), 'payable');
    }

    /**
     * This method ignores errors that occur when transferring funds.
     */
    public function safeTransfer(Wallet $wallet, string $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->transfer($wallet, $amount, $meta);
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function transfer(Wallet $wallet, string $amount, ?array $meta = null): Transfer
    {
        /** @var $this Wallet */
        app(CommonService::class)->verifyWithdraw($this, $amount);

        return $this->forceTransfer($wallet, $amount, $meta);
    }

    /**
     * Withdrawals from the system.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function withdraw(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        app(CommonService::class)->verifyWithdraw($this, $amount);

        return $this->forceWithdraw($amount, $meta, $confirmed);
    }

    /**
     * Checks if you can withdraw funds.
     */
    public function canWithdraw(string $amount, bool $allowZero = false): bool
    {
        $math = app(Mathable::class);

        /**
         * Allow to buy for free with a negative balance.
         */
        if ($allowZero && ! $math->compare($amount, 0)) {
            return true;
        }

        return $math->compare($this->balance, $amount) >= 0;
    }

    /**
     * Forced to withdraw funds from system.
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceWithdraw(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $self */
        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $amount, $meta, $confirmed) {
            return app(CommonService::class)
                ->forceWithdraw($self, $amount, $meta, $confirmed);
        });
    }

    /**
     * the forced transfer is needed when the user does not have the money and we drive it.
     * Sometimes you do. Depends on business logic.
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransfer(Wallet $wallet, string $amount, ?array $meta = null): Transfer
    {
        /** @var Wallet $self */
        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $amount, $wallet, $meta) {
            return app(CommonService::class)
                ->forceTransfer($self, $wallet, $amount, $meta);
        });
    }

    /**
     * the transfer table is used to confirm the payment
     * this method receives all transfers.
     */
    public function transfers(): MorphMany
    {
        /** @var Wallet $this */
        return app(WalletService::class)
            ->getWallet($this, false)
            ->morphMany(config('wallet.transfer.model', Transfer::class), 'from');
    }
}
