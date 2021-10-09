<?php

namespace Bavix\Wallet\Services;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Operation;
use function compact;
use function max;
use Throwable;

class CommonService
{
    private DbService $dbService;
    private LockService $lockService;
    private MathInterface $math;
    private WalletService $walletService;
    private Storable $store;
    private ConsistencyInterface $consistency;

    public function __construct(
        DbService $dbService,
        LockService $lockService,
        MathInterface $math,
        WalletService $walletService,
        Storable $store,
        ConsistencyInterface $consistency
    ) {
        $this->dbService = $dbService;
        $this->lockService = $lockService;
        $this->math = $math;
        $this->walletService = $walletService;
        $this->store = $store;
        $this->consistency = $consistency;
    }

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
        return $this->lockService->lock($this, __FUNCTION__, function () use ($from, $to, $amount, $meta, $status) {
            $discount = $this->walletService->discount($from, $to);
            $newAmount = max(0, $this->math->sub($amount, $discount));
            $fee = $this->walletService->fee($to, $newAmount);
            $this->consistency->checkPotential($from, $this->math->add($newAmount, $fee));

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
        return $this->lockService->lock($this, __FUNCTION__, function () use ($from, $to, $amount, $meta, $status) {
            $from = $this->walletService->getWallet($from);
            $discount = $this->walletService->discount($from, $to);
            $fee = $this->walletService->fee($to, $amount);

            $amount = max(0, $this->math->sub($amount, $discount));
            $withdraw = $this->forceWithdraw($from, $this->math->add($amount, $fee, $from->decimal_places), $meta);
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
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet, $amount, $meta, $confirmed) {
            $this->consistency->checkPositive($amount);

            /** @var WalletModel $wallet */
            $wallet = $this->walletService->getWallet($wallet);

            $transactions = $this->multiOperation($wallet, [
                app(Operation::class)
                    ->setType(Transaction::TYPE_WITHDRAW)
                    ->setConfirmed($confirmed)
                    ->setAmount($this->math->negative($amount))
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
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet, $amount, $meta, $confirmed) {
            $this->consistency->checkPositive($amount);

            /** @var WalletModel $wallet */
            $wallet = $this->walletService->getWallet($wallet);

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
     *
     * @deprecated
     * @see ConsistencyInterface::potential()
     *
     * @codeCoverageIgnore
     */
    public function verifyWithdraw(Wallet $wallet, $amount, bool $allowZero = false): void
    {
        $this->consistency->checkPotential($wallet, $amount, $allowZero);
    }

    /**
     * Create Operation without DB::transaction.
     *
     * @param Operation[] $operations
     *
     * @deprecated
     */
    public function multiOperation(Wallet $self, array $operations): array
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($self, $operations) {
            $amount = 0;
            $objects = [];
            foreach ($operations as $operation) {
                if ($operation->isConfirmed()) {
                    $amount = $this->math->add($amount, $operation->getAmount());
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
     *
     * @deprecated
     */
    public function assemble(array $brings): array
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($brings) {
            $self = $this;

            return $this->dbService->transaction(static function () use ($self, $brings) {
                return $self->multiBrings($brings);
            });
        });
    }

    /**
     * Create Bring without DB::transaction.
     *
     * @deprecated
     */
    public function multiBrings(array $brings): array
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($brings) {
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
     *
     * @deprecated
     */
    public function addBalance(Wallet $wallet, $amount): bool
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet, $amount) {
            /** @var WalletModel $wallet */
            $balance = $this->store->incBalance($wallet, $amount);

            try {
                $result = $wallet->newQuery()
                    ->whereKey($wallet->getKey())
                    ->update(compact('balance'))
                ;
            } catch (Throwable $throwable) {
                $this->store->setBalance($wallet, $wallet->getAvailableBalance());

                throw $throwable;
            }

            if ($result) {
                $wallet->fill(compact('balance'))
                    ->syncOriginalAttributes('balance')
                ;
            } else {
                $this->store->setBalance($wallet, $wallet->getAvailableBalance());
            }

            return $result;
        });
    }
}
