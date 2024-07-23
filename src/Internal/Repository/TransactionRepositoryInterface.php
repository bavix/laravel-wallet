<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Query\TransactionQueryInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * Inserts multiple transactions into the repository.
     *
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects The array of transaction objects to insert.
     */
    public function insert(array $objects): void;

    /**
     * Inserts a single transaction into the repository.
     *
     * @param TransactionDtoInterface $dto The transaction object to insert.
     * @return Transaction The inserted transaction object.
     */
    public function insertOne(TransactionDtoInterface $dto): Transaction;

    /**
     * Retrieves transactions from the repository based on the given query.
     *
     * @param TransactionQueryInterface $query The query to filter the transactions.
     * @return Transaction[] An array of transactions that match the query.
     */
    public function findBy(TransactionQueryInterface $query): array;
}
