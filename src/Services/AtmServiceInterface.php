<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface AtmServiceInterface
{
    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array;

    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array;
}
