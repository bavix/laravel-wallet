<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Models\Transfer;

interface PurchaseRepositoryInterface
{
    /**
     * @param non-empty-array<int, Transfer> $transfers
     */
    public function syncByTransfers(array $transfers): void;

    /**
     * @param non-empty-array<int> $transferIds
     */
    public function updateStatusByTransferIds(TransferStatus $status, array $transferIds): int;
}
