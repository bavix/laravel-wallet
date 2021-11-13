<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\PrepareServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Throwable;

final class CommonServiceLegacy
{
    private AtmServiceInterface $atmService;
    private CastServiceInterface $castService;
    private DatabaseServiceInterface $databaseService;
    private AssistantServiceInterface $assistantService;
    private PrepareServiceInterface $prepareService;
    private BookkeeperServiceInterface $bookkeeper;
    private TransferDtoAssemblerInterface $transferDtoAssembler;

    public function __construct(
        CastServiceInterface $castService,
        BookkeeperServiceInterface $bookkeeper,
        AssistantServiceInterface $satisfyService,
        DatabaseServiceInterface $databaseService,
        PrepareServiceInterface $prepareService,
        TransferDtoAssemblerInterface $transferDtoAssembler,
        AtmServiceInterface $atmService
    ) {
        $this->atmService = $atmService;
        $this->castService = $castService;
        $this->bookkeeper = $bookkeeper;
        $this->assistantService = $satisfyService;
        $this->databaseService = $databaseService;
        $this->prepareService = $prepareService;
        $this->transferDtoAssembler = $transferDtoAssembler;
    }

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransfer(Wallet $from, Wallet $to, $amount, ?array $meta = null, string $status = Transfer::STATUS_TRANSFER): Transfer
    {
        $transferLazyDto = $this->prepareService->transferLazy($from, $to, $status, $amount, $meta);
        $transfers = $this->applyTransfers([$transferLazyDto]);

        return current($transfers);
    }

    /**
     * @param non-empty-array<TransferLazyDto> $objects
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
     * @param int|string $amount
     *
     * @deprecated
     */
    public function addBalance(Wallet $wallet, $amount): bool
    {
        return $this->databaseService->transaction(function () use ($wallet, $amount) {
            /** @var WalletModel $wallet */
            $walletObject = $this->castService->getWallet($wallet);
            $balance = $this->bookkeeper->increase($walletObject, $amount);
            $result = 0;

            try {
                $result = $walletObject->newQuery()
                    ->whereKey($walletObject->getKey())
                    ->update(['balance' => $balance])
                ;

                $walletObject->fill(['balance' => $balance])->syncOriginalAttribute('balance');
            } finally {
                if ($result === 0) {
                    $this->bookkeeper->missing($walletObject);
                }
            }

            return (bool) $result;
        });
    }

    /**
     * @param float|int|string $amount
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
     * @param non-empty-array<int|string, Wallet>  $wallets
     * @param non-empty-array<int, TransactionDto> $objects
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

            $balance = $this->bookkeeper->increase($object, $total);

            $object->newQuery()->whereKey($object->getKey())->update(['balance' => $balance]); // ?qN
            $object->fill(['balance' => $balance])->syncOriginalAttribute('balance');
        }

        return $transactions;
    }
}
