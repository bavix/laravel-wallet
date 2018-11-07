<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * @var int
     */
    protected $cachedBalance;

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
        return DB::transaction(function () use ($amount, $wallet, $meta) {
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
        return DB::transaction(function () use ($amount, $wallet, $meta) {
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
            'uuid' => Str::uuid()->toString(),
        ]);
    }

    /**
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    protected function change(int $amount, ?array $meta, bool $confirmed): Transaction
    {
        $this->getBalanceAttribute();
        $this->cachedBalance += $amount;
        return $this->transactions()->create([
            'type' => $amount > 0 ? 'deposit' : 'withdraw',
            'payable_type' => $this->getMorphClass(),
            'payable_id' => $this->getKey(),
            'uuid' => Str::uuid()->toString(),
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
     * @return int
     */
    public function getBalanceAttribute(): int
    {
        if (!$this->cachedBalance) {
            $this->cachedBalance = $this->transactions()
                ->where('confirmed', true)
                ->sum('amount');
        }

        return $this->cachedBalance;
    }

    /**
     * @return void
     */
    public function resetBalance(): void
    {
        $this->cachedBalance = null;
    }

}
