<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Throwable;
use function app;
use function config;

/**
 * Trait HasWallet
 *
 * @package Bavix\Wallet\Traits
 *
 * @property-read Collection|WalletModel[] $wallets
 * @property-read int $balance
 */
trait HasWallet
{

    use MorphOneWallet;

    /**
     * The input means in the system
     *
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     * @throws
     */
    public function deposit(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $self = $this;
        return app(DbService::class)->transaction(static function () use ($self, $amount, $meta, $confirmed) {
            return app(CommonService::class)
                ->deposit($self, $amount, $meta, $confirmed);
        });
    }

    /**
     * Magic laravel framework method, makes it
     *  possible to call property balance
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
     * @return int
     * @throws
     */
    public function getBalanceAttribute(): int
    {
        return app(Storable::class)->getBalance($this);
    }

    /**
     * all user actions on wallets will be in this method
     *
     * @return MorphMany
     */
    public function transactions(): MorphMany
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model', Transaction::class), 'payable');
    }

    /**
     * This method ignores errors that occur when transferring funds
     *
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransfer(Wallet $wallet, int $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->transfer($wallet, $amount, $meta);
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host
     *
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     * @throws
     */
    public function transfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
    {
        app(CommonService::class)->verifyWithdraw($this, $amount);
        return $this->forceTransfer($wallet, $amount, $meta);
    }

    /**
     * Withdrawals from the system
     *
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function withdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        app(CommonService::class)->verifyWithdraw($this, $amount);
        return $this->forceWithdraw($amount, $meta, $confirmed);
    }

    /**
     * Checks if you can withdraw funds
     *
     * @param int $amount
     * @param bool $allowZero
     * @return bool
     */
    public function canWithdraw(int $amount, bool $allowZero = null): bool
    {
        /**
         * Allow to buy for free with a negative balance
         */
        if ($allowZero && $amount === 0) {
            return true;
        }

        return $this->balance >= $amount;
    }

    /**
     * Forced to withdraw funds from system
     *
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     * @throws
     */
    public function forceWithdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
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
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     * @throws
     */
    public function forceTransfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
    {
        $self = $this;
        return app(DbService::class)->transaction(static function () use ($self, $amount, $wallet, $meta) {
            return app(CommonService::class)
                ->forceTransfer($self, $wallet, $amount, $meta);
        });
    }

    /**
     * the transfer table is used to confirm the payment
     * this method receives all transfers
     *
     * @return MorphMany
     */
    public function transfers(): MorphMany
    {
        return app(WalletService::class)
            ->getWallet($this, false)
            ->morphMany(config('wallet.transfer.model', Transfer::class), 'from');
    }

}
