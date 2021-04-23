<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

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
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Traits\HasWallet;
use function compact;
use function max;
use Throwable;

/** @deprecated  */
class CommonService
{
    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function transfer(Wallet $from, Wallet $to, $amount, ?array $meta = null, string $status = Transfer::STATUS_TRANSFER): Transfer
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($from, $to, $amount, $meta, $status) {
            $math = app(Mathable::class);
            $discount = app(WalletService::class)->discount($from, $to);
            $newAmount = max(0, $math->sub($amount, $discount));
            $fee = app(WalletService::class)->fee($to, $newAmount);
            $this->verifyWithdraw($from, $math->add($newAmount, $fee));

            return $this->forceTransfer($from, $to, $amount, $meta, $status);
        });
    }

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransfer(Wallet $from, Wallet $to, $amount, ?array $meta = null, string $status = Transfer::STATUS_TRANSFER): Transfer
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($from, $to, $amount, $meta, $status) {
            $math = app(Mathable::class);
            $from = app(WalletService::class)->getWallet($from);
            $discount = app(WalletService::class)->discount($from, $to);
            $fee = app(WalletService::class)->fee($to, $amount);

            $amount = max(0, $math->sub($amount, $discount));
            $placesValue = app(FloatService::class)->exponent($from);
            $withdraw = $this->forceWithdraw($from, $math->add($amount, $fee, $placesValue), $meta);
            $deposit = $this->deposit($to, $amount, $meta);

            $transfers = $this->multiBrings([
                app(Bring::class)
                    ->setStatus($status)
                    ->setDeposit($deposit)
                    ->setWithdraw($withdraw)
                    ->setDiscount($discount)
                    ->setFrom($from)
                    ->setTo($to),
            ]);

            return current($transfers);
        });
    }

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function forceWithdraw(Wallet $wallet, $amount, ?array $meta, bool $confirmed = true): Transaction
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($wallet, $amount, $meta, $confirmed) {
            $walletService = app(WalletService::class);
            $walletService->checkAmount($amount);

            /** @var WalletModel $wallet */
            $wallet = $walletService->getWallet($wallet);

            $mathService = app(Mathable::class);
            $transactions = $this->multiOperation($wallet, [
                app(Operation::class)
                    ->setType(Transaction::TYPE_WITHDRAW)
                    ->setConfirmed($confirmed)
                    ->setAmount($mathService->negative($amount))
                    ->setMeta($meta),
            ]);

            return current($transactions);
        });
    }

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function deposit(Wallet $wallet, $amount, ?array $meta, bool $confirmed = true): Transaction
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($wallet, $amount, $meta, $confirmed) {
            $walletService = app(WalletService::class);
            $walletService->checkAmount($amount);

            /** @var WalletModel $wallet */
            $wallet = $walletService->getWallet($wallet);

            $transactions = $this->multiOperation($wallet, [
                app(Operation::class)
                    ->setType(Transaction::TYPE_DEPOSIT)
                    ->setConfirmed($confirmed)
                    ->setAmount($amount)
                    ->setMeta($meta),
            ]);

            return current($transactions);
        });
    }

    /**
     * @param int|string $amount
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function verifyWithdraw(Wallet $wallet, $amount, bool $allowZero = false): void
    {
        /**
         * @var HasWallet $wallet
         */
        if ($amount && !$wallet->balance) {
            throw new BalanceIsEmpty(trans('wallet::errors.wallet_empty'));
        }

        if (!$wallet->canWithdraw($amount, $allowZero)) {
            throw new InsufficientFunds(trans('wallet::errors.insufficient_funds'));
        }
    }

    /**
     * Create Operation without DB::transaction.
     *
     * @param Operation[] $operations
     */
    public function multiOperation(Wallet $self, array $operations): array
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($self, $operations) {
            $amount = 0;
            $objects = [];
            $math = app(Mathable::class);
            foreach ($operations as $operation) {
                if ($operation->isConfirmed()) {
                    $amount = $math->add($amount, $operation->getAmount());
                }

                $objects[] = $operation
                    ->setWallet($self)
                    ->create()
                ;
            }

            $this->addBalance($self, $amount);

            return $objects;
        });
    }

    /**
     * Create Bring with DB::transaction.
     *
     * @param Bring[] $brings
     *
     * @throws
     */
    public function assemble(array $brings): array
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($brings) {
            $self = $this;

            return app(DbService::class)->transaction(static function () use ($self, $brings) {
                return $self->multiBrings($brings);
            });
        });
    }

    /**
     * Create Bring without DB::transaction.
     */
    public function multiBrings(array $brings): array
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($brings) {
            $objects = [];
            foreach ($brings as $bring) {
                $objects[] = $bring->create();
            }

            return $objects;
        });
    }

    /**
     * @param int|string $amount
     *
     * @throws
     */
    public function addBalance(Wallet $wallet, $amount): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, static function () use ($wallet, $amount): bool {
            /** @var WalletModel $wallet */
            $balance = app(Storable::class)
                ->incBalance($wallet, $amount)
            ;

            try {
                $result = $wallet->newQuery()
                    ->whereKey($wallet->getKey())
                    ->update(compact('balance'))
                ;
            } catch (Throwable $throwable) {
                app(Storable::class)
                    ->setBalance($wallet, $wallet->getAvailableBalance())
                ;

                throw $throwable;
            }

            if ($result) {
                $wallet->fill(compact('balance'))
                    ->syncOriginalAttributes('balance')
                ;
            } else {
                app(Storable::class)
                    ->setBalance($wallet, $wallet->getAvailableBalance())
                ;
            }

            return (bool) $result;
        });
    }
}
