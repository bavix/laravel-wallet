<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Services\TransferServiceInterface;
use function config;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection;

/**
 * Trait HasWallet.
 *
 * @property Collection|WalletModel[] $wallets
 * @property string                   $balance
 * @property int                      $balanceInt
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Wallet
 */
trait HasWallet
{
    use MorphOneWallet;

    /**
     * The input means in the system.
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return app(AtomicServiceInterface::class)->block(
            $this,
            fn () => app(TransactionServiceInterface::class)
                ->makeOne($this, Transaction::TYPE_DEPOSIT, $amount, $meta, $confirmed)
        );
    }

    /**
     * Magic laravel framework method, makes it possible to call property balance.
     */
    public function getBalanceAttribute(): string
    {
        /** @var Wallet $this */
        return app(RegulatorServiceInterface::class)->amount(app(CastServiceInterface::class)->getWallet($this));
    }

    public function getBalanceIntAttribute(): int
    {
        return (int) $this->getBalanceAttribute();
    }

    /**
     * We receive transactions of the selected wallet.
     */
    public function walletTransactions(): HasMany
    {
        return app(CastServiceInterface::class)
            ->getWallet($this, false)
            ->hasMany(config('wallet.transaction.model', Transaction::class), 'wallet_id')
        ;
    }

    /**
     * all user actions on wallets will be in this method.
     */
    public function transactions(): MorphMany
    {
        return app(CastServiceInterface::class)
            ->getHolder($this)
            ->morphMany(config('wallet.transaction.model', Transaction::class), 'payable')
        ;
    }

    /**
     * This method ignores errors that occur when transferring funds.
     */
    public function safeTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer {
        try {
            return $this->transfer($wallet, $amount, $meta);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transfer(Wallet $wallet, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            /** @var Wallet $this */
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceTransfer($wallet, $amount, $meta);
        });
    }

    /**
     * Withdrawals from the system.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function withdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return app(AtomicServiceInterface::class)->block($this, function () use (
            $amount,
            $meta,
            $confirmed
        ): Transaction {
            /** @var Wallet $this */
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceWithdraw($amount, $meta, $confirmed);
        });
    }

    /**
     * Checks if you can withdraw funds.
     */
    public function canWithdraw(int|string $amount, bool $allowZero = false): bool
    {
        $mathService = app(MathServiceInterface::class);
        $wallet = app(CastServiceInterface::class)->getWallet($this);
        $balance = $mathService->add($this->getBalanceAttribute(), $wallet->getCreditAttribute());

        return app(ConsistencyServiceInterface::class)->canWithdraw($balance, $amount, $allowZero);
    }

    /**
     * Forced to withdraw funds from system.
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdraw(
        int|string $amount,
        array|null $meta = null,
        bool $confirmed = true
    ): Transaction {
        return app(AtomicServiceInterface::class)->block(
            $this,
            fn () => app(TransactionServiceInterface::class)
                ->makeOne($this, Transaction::TYPE_WITHDRAW, $amount, $meta, $confirmed)
        );
    }

    /**
     * the forced transfer is needed when the user does not have the money, and we drive it. Sometimes you do. Depends
     * on business logic.
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer {
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            $transferLazyDto = app(PrepareServiceInterface::class)
                ->transferLazy($this, $wallet, Transfer::STATUS_TRANSFER, $amount, $meta)
            ;

            $transfers = app(TransferServiceInterface::class)->apply([$transferLazyDto]);

            return current($transfers);
        });
    }

    /**
     * the transfer table is used to confirm the payment this method receives all transfers.
     */
    public function transfers(): MorphMany
    {
        /** @var Wallet $this */
        return app(CastServiceInterface::class)
            ->getWallet($this, false)
            ->morphMany(config('wallet.transfer.model', Transfer::class), 'from')
        ;
    }
}
