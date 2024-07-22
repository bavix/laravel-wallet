<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Query\TransactionQueryInterface;

interface TransactionQueryAssemblerInterface
{
    /**
     * Creates a new transaction query from the given uuids.
     *
     * @param non-empty-array<int|string, string> $uuids The uuids of the transactions.
     * @return TransactionQueryInterface The transaction query.
     */
    public function create(array $uuids): TransactionQueryInterface;
}
