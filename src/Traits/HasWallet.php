<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
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
     * @param int $amount
     * @return bool
     */
    public function canWithdraw(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     * @throws
     */
    public function transfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
    {
        return DB::transaction(function() use ($amount, $wallet, $meta) {
            $withdraw = $this->withdraw($amount, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($wallet, $withdraw, $deposit);
        });
    }

    /**
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
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
    {
        return DB::transaction(function() use ($amount, $wallet, $meta) {
            $withdraw = $this->forceWithdraw($amount, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($wallet, $withdraw, $deposit);
        });
    }

    /**
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
            'uuid' => Uuid::uuid4()->toString(),
        ]);
    }

    /**
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     * @throws
     */
    protected function change(int $amount, ?array $meta, bool $confirmed): Transaction
    {
        return DB::transaction(function () use ($amount, $meta, $confirmed) {
            if ($confirmed) {
                $this->addBalance($amount);
            }

            return $this->transactions()->create([
                'type' => $amount > 0 ? 'deposit' : 'withdraw',
                'payable_type' => $this->getMorphClass(),
                'payable_id' => $this->getKey(),
                'uuid' => Uuid::uuid4()->toString(),
                'confirmed' => $confirmed,
                'amount' => $amount,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * @return MorphMany
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(config('wallet.transaction.model'), 'payable');
    }

    /**
     * @return MorphMany
     */
    public function transfers(): MorphMany
    {
        return $this->morphMany(config('wallet.transfer.model'), 'from');
    }

    /**
     * @return MorphMany
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(config('wallet.wallet.model'), 'holder');
    }

    /**
     * @return MorphOne
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
     * Example:
     *  $user1 = User::first()->load('balance');
     *  $user2 = User::first()->load('balance');
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
     */
    public function getBalanceAttribute(): int
    {
        if ($this instanceof WalletModel) {
            return (int) ($this->attributes['balance'] ?? 0);
        }

        if (!\array_key_exists('wallet', $this->relations)) {
            $this->load('wallet');
        }

        return $this->wallet->balance;
    }

    /**
     * @param int $amount
     * @return bool
     */
    protected function addBalance(int $amount): bool
    {
        $wallet = $this;

        if (!($this instanceof WalletModel)) {
            $this->getBalanceAttribute();
            $wallet = $this->wallet;
        }

        $wallet->balance += $amount;
        return $wallet->save();
    }

}
