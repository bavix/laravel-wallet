<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Tax;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
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
     * The variable is used for the cache, so as not to request wallets many times.
     * WalletProxy keeps the money wallets in the memory to avoid errors when you
     * purchase/transfer, etc.
     *
     * @var array
     */
    private $_wallets = [];

    /**
     * The amount of checks for errors
     *
     * @param int $amount
     * @throws
     */
    private function checkAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new AmountInvalid('The amount must be greater than zero');
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
        return $this->change(-$amount, $meta, $confirmed);
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
        return $this->change($amount, $meta, $confirmed);
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
        if (!$this->canWithdraw($amount)) {
            throw new BalanceIsEmpty('Balance insufficient for write-off');
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
        return DB::transaction(function() use ($amount, $wallet, $meta) {
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
        return DB::transaction(function() use ($amount, $wallet, $meta) {
            $fee = Tax::fee($wallet, $amount);
            $withdraw = $this->forceWithdraw($amount + $fee, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($wallet, $withdraw, $deposit);
        });
    }

    /**
     * this method adds a new transfer to the transfer table
     *
     * @param Wallet $wallet
     * @param Transaction $withdraw
     * @param Transaction $deposit
     * @return Transfer
     * @throws
     */
    protected function assemble(Wallet $wallet, Transaction $withdraw, Transaction $deposit): Transfer
    {
        /**
         * @var Model $wallet
         */
        return \app(config('wallet.transfer.model'))->create([
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
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     * @throws
     */
    protected function change(int $amount, ?array $meta, bool $confirmed): Transaction
    {
        return DB::transaction(function() use ($amount, $meta, $confirmed) {

            if ($this instanceof WalletModel) {
                $payable = $this->holder;
                $wallet = $this;
            } else {
                $payable = $this;
                $wallet = $this->wallet;
            }

            if ($confirmed) {
                $this->addBalance($wallet, $amount);
            }

            return $this->transactions()->create([
                'type' => $amount > 0 ? 'deposit' : 'withdraw',
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->getKey(),
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
        return $this->morphMany(config('wallet.transaction.model'), 'payable');
    }

    /**
     * the transfer table is used to confirm the payment
     * this method receives all transfers
     *
     * @return MorphMany
     */
    public function transfers(): MorphMany
    {
        return $this->morphMany(config('wallet.transfer.model'), 'from');
    }

    /**
     * method of obtaining all wallets
     *
     * @return MorphMany
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(config('wallet.wallet.model'), 'holder');
    }

    /**
     * Get wallet by slug
     *
     *  $user->wallet->balance // 200
     *  or short recording $user->balance; // 200
     *
     *  $defaultSlug = config('wallet.wallet.default.slug');
     *  $user->getWallet($defaultSlug)->balance; // 200
     *
     *  $user->getWallet('usd')->balance; // 50
     *  $user->getWallet('rub')->balance; // 100
     *
     * @param string $slug
     * @return WalletModel|null
     */
    public function getWallet(string $slug): ?WalletModel
    {
        if (!\array_key_exists($slug, $this->_wallets)) {
            $this->_wallets[$slug] = $this->wallets()
                ->where('slug', $slug)
                ->first();
        }

        return $this->_wallets[$slug];
    }

    /**
     * Get default Wallet
     * this method is used for Eager Loading
     *
     * @return MorphOne|WalletModel
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(config('wallet.wallet.model'), 'holder')
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
                WalletProxy::set($this->getKey(), (int) ($this->attributes['balance'] ?? 0));
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

        if ($wallet->save()) {
            WalletProxy::set($wallet->getKey(), $newBalance);
            return true;
        }

        return false;
    }

}
