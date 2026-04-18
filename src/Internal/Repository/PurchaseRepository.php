<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Purchase;

final readonly class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(
        private Purchase $purchase,
        private ClockServiceInterface $clockService,
    ) {
    }

    public function syncByTransfers(array $transfers): void
    {
        $now = $this->clockService->now();
        $rows = [];
        foreach ($transfers as $transfer) {
            $rows[] = [
                'transfer_id' => $transfer->getKey(),
                'from_id' => $transfer->from_id,
                'to_id' => $transfer->to_id,
                'status' => $transfer->status->value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->purchase->newQuery()
            ->upsert($rows, ['transfer_id'], ['from_id', 'to_id', 'status', 'updated_at']);
    }

    public function updateStatusByTransferIds(TransferStatus $status, array $transferIds): int
    {
        return $this->purchase->newQuery()
            ->whereIn('transfer_id', $transferIds)
            ->update([
                'status' => $status->value,
                'updated_at' => $this->clockService->now(),
            ]);
    }
}
