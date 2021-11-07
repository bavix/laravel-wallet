<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Query\TransactionQuery;
use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Internal\Repository\TransactionRepository;
use Bavix\Wallet\Internal\Repository\TransferRepository;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

/** @psalm-internal */
class AtmService
{
    private TransactionRepository $transactionRepository;
    private TransferRepository $transferRepository;
    private AssistantService $assistantService;
    private BookkeeperInterface $bookkeeper;

    public function __construct(
        TransactionRepository $transactionRepository,
        TransferRepository $transferRepository,
        AssistantService $assistantService,
        BookkeeperInterface $bookkeeper
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transferRepository = $transferRepository;
        $this->assistantService = $assistantService;
        $this->bookkeeper = $bookkeeper;
    }

    /**
     * @param non-empty-array<int|string, TransactionDto> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array
    {
        $this->transactionRepository->insert($objects);
        $uuids = $this->assistantService->getUuids($objects);
        $query = new TransactionQuery($uuids);

        $items = $this->transactionRepository->findBy($query);
        assert(count($items) === count($uuids));

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }

    /**
     * @param non-empty-array<int|string, TransferDto> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array
    {
        $this->transferRepository->insert($objects);
        $uuids = $this->assistantService->getUuids($objects);
        $query = new TransferQuery($uuids);

        $items = $this->transferRepository->findBy($query);
        assert(count($items) === count($uuids));

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }
}
