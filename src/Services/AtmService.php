<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Assembler\TransactionQueryAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferQueryAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

/** @psalm-internal */
final class AtmService implements AtmServiceInterface
{
    private TransactionQueryAssemblerInterface $transactionQueryAssembler;
    private TransferQueryAssemblerInterface $transferQueryAssembler;
    private TransactionRepositoryInterface $transactionRepository;
    private TransferRepositoryInterface $transferRepository;
    private AssistantServiceInterface $assistantService;

    public function __construct(
        TransactionQueryAssemblerInterface $transactionQueryAssembler,
        TransferQueryAssemblerInterface $transferQueryAssembler,
        TransactionRepositoryInterface $transactionRepository,
        TransferRepositoryInterface $transferRepository,
        AssistantServiceInterface $assistantService
    ) {
        $this->transactionQueryAssembler = $transactionQueryAssembler;
        $this->transferQueryAssembler = $transferQueryAssembler;
        $this->transactionRepository = $transactionRepository;
        $this->transferRepository = $transferRepository;
        $this->assistantService = $assistantService;
    }

    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array
    {
        if (count($objects) === 1) {
            $items = [$this->transactionRepository->insertOne(reset($objects))];
        } else {
            $this->transactionRepository->insert($objects);
            $uuids = $this->assistantService->getUuids($objects);
            $query = $this->transactionQueryAssembler->create($uuids);
            $items = $this->transactionRepository->findBy($query);
        }

        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }

    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array
    {
        if (count($objects) === 1) {
            $items = [$this->transferRepository->insertOne(reset($objects))];
        } else {
            $this->transferRepository->insert($objects);
            $uuids = $this->assistantService->getUuids($objects);
            $query = $this->transferQueryAssembler->create($uuids);
            $items = $this->transferRepository->findBy($query);
        }

        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }
}
