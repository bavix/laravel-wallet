<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Query\TransactionQueryInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     */
    public function insert(array $objects): void;

    public function insertOne(TransactionDtoInterface $dto): Transaction;

    /** @return Transaction[] */
    public function findBy(TransactionQueryInterface $query): array;
}
