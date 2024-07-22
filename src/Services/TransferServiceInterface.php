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
     * Updates the status of transfers identified by their IDs.
     *
     * This method updates the status field of transfers in the repository
     * to the provided status, for all transfers whose IDs are included
     * in the provided array. The method returns `true` if at least one
     * transfer was updated, or `false` if no transfers were updated.
     *
     * @param string $status The new status to set for the specified transfers.
     * @param int[] $ids A non-empty array of transfer IDs to update.
     * @return bool `true` if at least one transfer was updated, `false` otherwise.
     */
    public function updateStatusByIds(string $status, array $ids): bool;

    /**
     * Applies a set of transfer operations in a single database transaction.
     *
     * This method takes an array of transfer objects and applies them,
     * creating transfers and corresponding transactions.
     *
     * @param non-empty-array<TransferLazyDtoInterface> $objects The array of transfer operations to apply.
     * @return non-empty-array<string, Transfer> An array of created transfers, indexed by their IDs.
     *
     * @throws RecordNotFoundException If a wallet referenced in the transfer operations is not found.
     * @throws RecordsNotFoundException If a wallet referenced in the transfer operations is not found.
     * @throws TransactionFailedException If the transaction fails for any reason.
     * @throws ExceptionInterface If an unexpected error occurs.
     */
    public function apply(array $objects): array;
}
