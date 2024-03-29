<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @api
 */
interface TransferServiceInterface
{
    /**
     * @param int[] $ids
     */
    public function updateStatusByIds(string $status, array $ids): bool;

    /**
     * @param non-empty-array<TransferLazyDtoInterface> $objects
     * @return non-empty-array<string, Transfer>
     *
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function apply(array $objects): array;
}
