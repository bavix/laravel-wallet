<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

/**
 * @internal
 */
final readonly class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(
        private CastServiceInterface $castService
    ) {
    }

    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array
    {
        $status = $gifts
            ? [TransferStatus::Paid->value, TransferStatus::Gift->value]
            : [TransferStatus::Paid->value];

        $productCounts = [];
        $total = 0;
        foreach ($basketDto->items() as $itemDto) {
            $wallet = $this->castService->getWallet($itemDto->getReceiving() ?? $itemDto->getProduct());
            $walletId = $wallet->getKey();
            $productCounts[$walletId] = ($productCounts[$walletId] ?? 0) + count($itemDto);
            $total += count($itemDto);
        }

        $transfers = $customer->transfers()
            ->with(['deposit', 'withdraw.wallet'])
            ->whereIn('to_id', array_keys($productCounts))
            ->whereIn('status', $status)
            ->orderBy('id', 'desc')
            ->limit($total)
            ->get();

        $selected = [];
        foreach ($transfers as $transfer) {
            $toId = $transfer->to_id;
            if (! array_key_exists($toId, $productCounts) || $productCounts[$toId] <= 0) {
                continue;
            }

            $selected[] = $transfer;
            $productCounts[$toId]--;
        }

        return $selected;
    }
}
