<?php

namespace Bavix\Wallet\Services;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\AssistantService;
use Bavix\Wallet\Internal\Service\AtmService;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Internal\Service\PrepareService;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Bring;
use function compact;
use function max;
use Throwable;

class CommonService
{
    private DbService $dbService;
    private LockService $lockService;
    private MathInterface $math;
    private AtmService $atmService;
    private CastService $castService;
    private WalletService $walletService;
    private AssistantService $assistantService;
    private PrepareService $prepareService;
    private BookkeeperInterface $bookkeeper;
    private ConsistencyInterface $consistency;
    private TransferDtoAssembler $transferDtoAssembler;

    public function __construct(
        DbService $dbService,
        LockService $lockService,
        MathInterface $math,
        CastService $castService,
        WalletService $walletService,
        BookkeeperInterface $bookkeeper,
        ConsistencyInterface $consistency,
        AssistantService $satisfyService,
        PrepareService $prepareService,
        TransferDtoAssembler $transferDtoAssembler,
        AtmService $atmService
    ) {
        $this->dbService = $dbService;
        $this->lockService = $lockService;
        $this->math = $math;
        $this->atmService = $atmService;
        $this->castService = $castService;
        $this->walletService = $walletService;
        $this->bookkeeper = $bookkeeper;
        $this->consistency = $consistency;
        $this->assistantService = $satisfyService;
        $this->prepareService = $prepareService;
        $this->transferDtoAssembler = $transferDtoAssembler;
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

            $withdrawDto = $this->prepareService->withdraw($from, $this->math->add($amount, $fee, $from->decimal_places), $meta);
            $depositDto = $this->prepareService->deposit($to, $amount, $meta);
            $transactions = $this->applyOperations(
                [$withdrawDto->getWalletId() => $from, $depositDto->getWalletId() => $to],
                [$withdrawDto, $depositDto],
            );

            $withdraw = $transactions[$withdrawDto->getUuid()] ?? null;
            assert($withdraw !== null);

            $deposit = $transactions[$depositDto->getUuid()] ?? null;
            assert($deposit !== null);

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
     * Create Bring with DB::transaction.
     *
     * @param Bring[] $brings
     *
     * @deprecated
     * @see AtmService::makeTransfers()
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
     * @param non-empty-array<mixed, Bring> $brings
     *
     * @deprecated
     * @see AtmService::makeTransfers()
     */
    public function multiBrings(array $brings): array
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($brings) {
            $objects = [];
            foreach ($brings as $bring) {
                $object = $this->transferDtoAssembler->create(
                    $bring->getDeposit()->getKey(),
                    $bring->getWithdraw()->getKey(),
                    $bring->getStatus(),
                    $this->castService->getModel($bring->getFrom()),
                    $this->castService->getModel($bring->getTo()),
                    $bring->getDiscount(),
                    $bring->getFee()
                );

                $objects[$object->getUuid()] = $object;
            }

            return $this->atmService->makeTransfers($objects);
        });
    }

    /**
     * @param int|string $amount
     *
     * @deprecated
     */
    public function addBalance(Wallet $wallet, $amount): bool
    {
        return $this->lockService->lock($this, __FUNCTION__, function () use ($wallet, $amount) {
            /** @var WalletModel $wallet */
            $walletObject = $this->walletService->getWallet($wallet);
            $balance = $this->bookkeeper->increase($walletObject, $amount);

            try {
                $result = $wallet->newQuery()
                    ->whereKey($wallet->getKey())
                    ->update(compact('balance'))
                ;
            } catch (Throwable $throwable) {
                $this->bookkeeper->sync($walletObject, $wallet->getAvailableBalance());

                throw $throwable;
            }

            if ($result) {
                $wallet->fill(compact('balance'))
                    ->syncOriginalAttributes('balance')
                ;
            } else {
                $this->bookkeeper->sync($walletObject, $wallet->getAvailableBalance());
            }

            return $result;
        });
    }

    /**
     * @param float|int|string $amount
     */
    public function makeOperation(Wallet $wallet, string $type, $amount, ?array $meta, bool $confirmed = true): Transaction
    {
        assert(in_array($type, [Transaction::TYPE_DEPOSIT, Transaction::TYPE_WITHDRAW], true));

        if ($type === Transaction::TYPE_DEPOSIT) {
            $dto = $this->prepareService->deposit($wallet, (string) $amount, $meta, $confirmed);
        } else {
            $dto = $this->prepareService->withdraw($wallet, (string) $amount, $meta, $confirmed);
        }

        $transactions = $this->applyOperations(
            [$dto->getWalletId() => $wallet],
            [$dto],
        );

        return current($transactions);
    }

    /**
     * @param non-empty-array<int, Wallet>         $wallets
     * @param non-empty-array<int, TransactionDto> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function applyOperations(array $wallets, array $objects): array
    {
        $transactions = $this->atmService->makeTransactions($objects); // q1
        $totals = $this->assistantService->getSums($objects);

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet !== null);

            $object = $this->walletService->getWallet($wallet);
            $balance = $this->bookkeeper->increase($object, $total);

            $object->newQuery()->update(compact('balance')); // ?qN
            $object->fill(compact('balance'))->syncOriginalAttribute('balance');
        }

        return $transactions;
    }
}
