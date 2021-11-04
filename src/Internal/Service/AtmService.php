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
    private BookkeeperInterface $bookkeeper;

    public function __construct(
        TransactionRepository $transactionRepository,
        TransferRepository $transferRepository,
        BookkeeperInterface $bookkeeper
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transferRepository = $transferRepository;
        $this->bookkeeper = $bookkeeper;
    }

    /**
     * @param non-empty-array<string, TransactionDto> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array
    {
        $this->transactionRepository->insert($objects);
        $query = new TransactionQuery(array_keys($objects));

        $items = $this->transactionRepository->findBy($query);
        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }

    /**
     * @param non-empty-array<string, TransferDto> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array
    {
        $this->transferRepository->insert($objects);
        $query = new TransferQuery(array_keys($objects));

        $items = $this->transferRepository->findBy($query);
        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }
}
