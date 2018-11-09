<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Class HasWallet
 *
 * @package Bavix\Wallet\Traits
 *
 * @property-read int $balance
 */
trait HasWallet
{

    /**
     * @var array
     */
    protected static $cachedBalances = [];

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
        $this->getBalanceAttribute();
        static::$cachedBalances[$this->getKey()] += $amount;
        return $this->transactions()->create([
            'type' => $amount > 0 ? 'deposit' : 'withdraw',
            'payable_type' => $this->getMorphClass(),
            'payable_id' => $this->getKey(),
            'uuid' => Uuid::uuid4()->toString(),
            'confirmed' => $confirmed,
            'amount' => $amount,
            'meta' => $meta,
        ]);
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
    public function balance(): MorphMany
    {
        return $this->transactions()
            ->selectRaw('payable_id, sum(amount) as total')
            ->where('confirmed', true)
            ->groupBy('payable_id');
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
        if (!\array_key_exists($this->getKey(), static::$cachedBalances)) {
            if (!\array_key_exists('balance', $this->relations)) {
                $this->load('balance');
            }

            /**
             * @var Collection $collection
             */
            $collection = $this->getRelation('balance');
            $relation = $collection->first();
            static::$cachedBalances[$this->getKey()] = (int) ($relation->total ?? 0);
        }

        return static::$cachedBalances[$this->getKey()];
    }

}
