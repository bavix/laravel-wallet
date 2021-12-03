<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

/** @deprecated */
final class CommonServiceLegacy
{
    private AtmServiceInterface $atmService;
    private CastServiceInterface $castService;
    private DatabaseServiceInterface $databaseService;
    private AssistantServiceInterface $assistantService;
    private PrepareServiceInterface $prepareService;
    private RegulatorServiceInterface $regulatorService;
    private TransferDtoAssemblerInterface $transferDtoAssembler;

    public function __construct(
        CastServiceInterface $castService,
        AssistantServiceInterface $satisfyService,
        DatabaseServiceInterface $databaseService,
        PrepareServiceInterface $prepareService,
        TransferDtoAssemblerInterface $transferDtoAssembler,
        RegulatorServiceInterface $regulatorService,
        AtmServiceInterface $atmService
    ) {
        $this->atmService = $atmService;
        $this->castService = $castService;
        $this->assistantService = $satisfyService;
        $this->databaseService = $databaseService;
        $this->prepareService = $prepareService;
        $this->regulatorService = $regulatorService;
        $this->transferDtoAssembler = $transferDtoAssembler;
    }

    /**
     * @param int|string $amount
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransfer(Wallet $from, Wallet $to, $amount, ?array $meta = null, string $status = Transfer::STATUS_TRANSFER): Transfer
    {
        $transferLazyDto = $this->prepareService->transferLazy($from, $to, $status, $amount, $meta);
        $transfers = $this->applyTransfers([$transferLazyDto]);

        return current($transfers);
    }

    /**
     * @param non-empty-array<TransferLazyDtoInterface> $objects
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function applyTransfers(array $objects): array
    {
        return $this->databaseService->transaction(function () use ($objects): array {
            $wallets = [];
            $operations = [];
            foreach ($objects as $object) {
                $fromWallet = $this->castService->getWallet($object->getFromWallet());
                $wallets[$fromWallet->getKey()] = $fromWallet;

                $toWallet = $this->castService->getWallet($object->getToWallet());
                $wallets[$toWallet->getKey()] = $toWallet;

                $operations[] = $object->getWithdrawDto();
                $operations[] = $object->getDepositDto();
            }

            $transactions = $this->applyTransactions($wallets, $operations);

            $transfers = [];
            foreach ($objects as $object) {
                $withdraw = $transactions[$object->getWithdrawDto()->getUuid()] ?? null;
                assert($withdraw !== null);

                $deposit = $transactions[$object->getDepositDto()->getUuid()] ?? null;
                assert($deposit !== null);

                $transfers[] = $this->transferDtoAssembler->create(
                    $deposit->getKey(),
                    $withdraw->getKey(),
                    $object->getStatus(),
                    $this->castService->getModel($object->getFromWallet()),
                    $this->castService->getModel($object->getToWallet()),
                    $object->getDiscount(),
                    $object->getFee()
                );
            }

            return $this->atmService->makeTransfers($transfers);
        });
    }

    /**
     * @param float|int|string $amount
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function makeTransaction(Wallet $wallet, string $type, $amount, ?array $meta, bool $confirmed = true): Transaction
    {
        assert(in_array($type, [Transaction::TYPE_DEPOSIT, Transaction::TYPE_WITHDRAW], true));

        if ($type === Transaction::TYPE_DEPOSIT) {
            $dto = $this->prepareService->deposit($wallet, (string) $amount, $meta, $confirmed);
        } else {
            $dto = $this->prepareService->withdraw($wallet, (string) $amount, $meta, $confirmed);
        }

        $transactions = $this->applyTransactions(
            [$dto->getWalletId() => $wallet],
            [$dto],
        );

        return current($transactions);
    }

    /**
     * @param non-empty-array<int|string, Wallet>           $wallets
     * @param non-empty-array<int, TransactionDtoInterface> $objects
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     *
     * @return non-empty-array<string, Transaction>
     */
    public function applyTransactions(array $wallets, array $objects): array
    {
        $transactions = $this->atmService->makeTransactions($objects); // q1
        $totals = $this->assistantService->getSums($objects);

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet !== null);

            $object = $this->castService->getWallet($wallet);
            assert((int) $object->getKey() === $walletId);

            $this->regulatorService->increase($object, $total);
        }

        return $transactions;
    }
}
