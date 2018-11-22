<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Tax;
use Bavix\Wallet\WalletProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Trait HasWallet
 *
 * @package Bavix\Wallet\Traits
 *
 * @property-read WalletModel $wallet
 * @property-read Collection|WalletModel[] $wallets
 * @property-read int $balance
 */
trait HasWallet
{

    /**
     * The amount of checks for errors
     *
     * @param int $amount
     * @throws
     */
    private function checkAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    /**
     * Forced to withdraw funds from system
     *
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function forceWithdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $this->checkAmount($amount);
        return $this->change(Transaction::TYPE_WITHDRAW, -$amount, $meta, $confirmed);
    }

    /**
     * The input means in the system
     *
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function deposit(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $this->checkAmount($amount);
        return $this->change(Transaction::TYPE_DEPOSIT, $amount, $meta, $confirmed);
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
        if ($amount && !$this->balance) {
            throw new BalanceIsEmpty(trans('wallet::errors.wallet_empty'));
        }

        if (!$this->canWithdraw($amount)) {
            throw new InsufficientFunds(trans('wallet::errors.insufficient_funds'));
        }

        return $this->forceWithdraw($amount, $meta, $confirmed);
    }

    /**
     * Checks if you can withdraw funds
     *
     * @param int $amount
     * @return bool
     */
    public function canWithdraw(int $amount): bool
    {
        return $this->balance >= $amount;
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
        return DB::transaction(function () use ($amount, $wallet, $meta) {
            $fee = Tax::fee($wallet, $amount);
            $withdraw = $this->withdraw($amount + $fee, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($wallet, $withdraw, $deposit);
        });
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
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * the forced transfer is needed when the user does not have the money and we drive it.
     * Sometimes you do. Depends on business logic.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
    {
        return DB::transaction(function () use ($amount, $wallet, $meta) {
            $fee = Tax::fee($wallet, $amount);
            $withdraw = $this->forceWithdraw($amount + $fee, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($wallet, $withdraw, $deposit);
        });
    }

    /**
     * this method adds a new transfer to the transfer table
     *
     * @param string $status
     * @param Wallet $wallet
     * @param Transaction $withdraw
     * @param Transaction $deposit
     * @return Transfer
     * @throws
     */
    protected function assemble(Wallet $wallet, Transaction $withdraw, Transaction $deposit): Transfer
    {
        $status = Transfer::STATUS_PAID;
        if ($this->getMorphClass() !== $withdraw->payable_type ||
            $this->getKey() !== $withdraw->payable_id) {
            $status = Transfer::STATUS_GIFT;
        }

        /**
         * @var Model $wallet
         */
        return \app('bavix.wallet::transfer')->create([
            'status' => $status,
            'deposit_id' => $deposit->getKey(),
            'withdraw_id' => $withdraw->getKey(),
            'from_type' => $this->getMorphClass(),
            'from_id' => $this->getKey(),
            'to_type' => $wallet->getMorphClass(),
            'to_id' => $wallet->getKey(),
            'fee' => $withdraw->amount - $deposit->amount,
            'uuid' => Uuid::uuid4()->toString(),
        ]);
    }

    /**
     * this method adds a new transaction to the translation table
     *
     * @param string $type
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     * @throws
     */
    protected function change(string $type, int $amount, ?array $meta, bool $confirmed): Transaction
    {
        return DB::transaction(function () use ($type, $amount, $meta, $confirmed) {

            $wallet = $this;
            if (!($this instanceof WalletModel)) {
                $wallet = $this->wallet;
            }

            if ($confirmed) {
                $this->addBalance($wallet, $amount);
            }

            return $this->transactions()->create([
                'type' => $type,
                'wallet_id' => $wallet->getKey(),
                'uuid' => Uuid::uuid4()->toString(),
                'confirmed' => $confirmed,
                'amount' => $amount,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * all user actions on wallets will be in this method
     *
     * @return MorphMany
     */
    public function transactions(): MorphMany
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable');
    }

    /**
     * the transfer table is used to confirm the payment
     * this method receives all transfers
     *
     * @return MorphMany
     */
    public function transfers(): MorphMany
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphMany(config('wallet.transfer.model'), 'from');
    }

    /**
     * Get default Wallet
     * this method is used for Eager Loading
     *
     * @return MorphOne|WalletModel
     */
    public function wallet(): MorphOne
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphOne(config('wallet.wallet.model'), 'holder')
            ->withDefault([
                'name' => config('wallet.wallet.default.name'),
                'slug' => config('wallet.wallet.default.slug'),
                'balance' => 0,
            ]);
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
        if ($this instanceof WalletModel) {
            $this->exists or $this->save();
            if (!WalletProxy::has($this->getKey())) {
                WalletProxy::set($this->getKey(), (int)($this->attributes['balance'] ?? 0));
            }

            return WalletProxy::get($this->getKey());
        }

        return $this->wallet->balance;
    }

    /**
     * This method automatically updates the balance in the
     * database and the project statics
     *
     * @param WalletModel $wallet
     * @param int $amount
     * @return bool
     */
    protected function addBalance(WalletModel $wallet, int $amount): bool
    {
        $newBalance = $this->getBalanceAttribute() + $amount;
        $wallet->balance = $newBalance;

        return
            // update database wallet
            $wallet->save() &&

            // update static wallet
            WalletProxy::set($wallet->getKey(), $newBalance);
    }

}
