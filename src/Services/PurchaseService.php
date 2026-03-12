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

        $walletIds = [];
        foreach ($basketDto->items() as $itemDto) {
            $wallet = $this->castService->getWallet($itemDto->getReceiving() ?? $itemDto->getProduct());
            $walletId = $wallet->getKey();
            $walletIds[$walletId] = true;
        }

        /** @var array<int, array<int, \Bavix\Wallet\Models\Transfer>> $groupedByWallet */
        $groupedByWallet = [];
        $transfers = $customer->transfers()
            ->with(['deposit', 'withdraw.wallet'])
            ->whereIn('to_id', array_keys($walletIds))
            ->whereIn('status', $status)
            ->orderBy('id', 'desc')
            ->get();
        foreach ($transfers as $transfer) {
            $groupedByWallet[$transfer->to_id] ??= [];
            $groupedByWallet[$transfer->to_id][] = $transfer;
        }

        $selected = [];
        foreach ($basketDto->items() as $itemDto) {
            $wallet = $this->castService->getWallet($itemDto->getReceiving() ?? $itemDto->getProduct());
            $walletId = $wallet->getKey();

            foreach ($itemDto->getItems() as $_product) {
                if (! array_key_exists($walletId, $groupedByWallet)) {
                    continue;
                }
                if ($groupedByWallet[$walletId] === []) {
                    continue;
                }

                /** @var \Bavix\Wallet\Models\Transfer $transfer */
                $transfer = array_shift($groupedByWallet[$walletId]);
                $selected[] = $transfer;
            }
        }

        return $selected;
    }
}
