<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Query\TransactionQuery;
use Bavix\Wallet\Models\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransactionDto> $objects
     */
    public function insert(array $objects): void;

    /** @return Transaction[] */
    public function findBy(TransactionQuery $query): array;
}
