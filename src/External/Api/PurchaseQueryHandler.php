<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Models\Purchase;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CastServiceInterface;

/**
 * @internal
 */
final readonly class PurchaseQueryHandler implements PurchaseQueryHandlerInterface
{
    public function __construct(
        private CastServiceInterface $castService,
        private Purchase $purchase,
        private Transfer $transfer
    ) {
    }

    public function apply(array $objects): array
    {
        $queryMap = [];
        $fromIds = [];
        $toIds = [];

        foreach ($objects as $index => $object) {
            $fromId = $this->castService->getWallet($object->getCustomer())
                ->getKey();
            $toId = $this->castService->getWallet($object->getReceiving() ?? $object->getProduct())
                ->getKey();

            $fromIds[$fromId] = true;
            $toIds[$toId] = true;

            $queryMap[$index] = [
                'strict' => $this->key($fromId, $toId, false),
                'gifts' => $this->key($fromId, $toId, true),
                'includeGifts' => $object->includeGifts(),
            ];
        }

        $matchedTransferIds = [];
        $purchases = $this->purchase->newQuery()
            ->select(['id', 'transfer_id', 'from_id', 'to_id', 'status'])
            ->whereIn('from_id', array_keys($fromIds))
            ->whereIn('to_id', array_keys($toIds))
            ->whereIn('status', [TransferStatus::Paid->value, TransferStatus::Gift->value])
            ->orderByDesc('id')
            ->get();

        foreach ($purchases as $purchase) {
            $strictKey = $this->key($purchase->from_id, $purchase->to_id, false);
            if ($purchase->status === TransferStatus::Paid && ! array_key_exists($strictKey, $matchedTransferIds)) {
                $matchedTransferIds[$strictKey] = $purchase->transfer_id;
            }

            $giftsKey = $this->key($purchase->from_id, $purchase->to_id, true);
            if (! array_key_exists($giftsKey, $matchedTransferIds)) {
                $matchedTransferIds[$giftsKey] = $purchase->transfer_id;
            }
        }

        $needFallback = [];
        foreach ($queryMap as $item) {
            $strict = $item['strict'];
            if (! array_key_exists($strict, $matchedTransferIds)) {
                $needFallback[$strict] = true;
            }

            $gifts = $item['gifts'];
            if ($item['includeGifts'] && ! array_key_exists($gifts, $matchedTransferIds)) {
                $needFallback[$gifts] = true;
            }
        }

        if ($needFallback !== []) {
            $transfers = $this->transfer->newQuery()
                ->select([$this->transfer->getKeyName(), 'from_id', 'to_id', 'status'])
                ->whereIn('from_id', array_keys($fromIds))
                ->whereIn('to_id', array_keys($toIds))
                ->whereIn('status', [TransferStatus::Paid->value, TransferStatus::Gift->value])
                ->orderByDesc($this->transfer->getKeyName())
                ->get();

            foreach ($transfers as $transfer) {
                $strictKey = $this->key($transfer->from_id, $transfer->to_id, false);
                if (
                    $transfer->status === TransferStatus::Paid
                    && array_key_exists($strictKey, $needFallback)
                    && ! array_key_exists($strictKey, $matchedTransferIds)
                ) {
                    $matchedTransferIds[$strictKey] = $transfer->getKey();
                }

                $giftsKey = $this->key($transfer->from_id, $transfer->to_id, true);
                if (array_key_exists($giftsKey, $needFallback) && ! array_key_exists($giftsKey, $matchedTransferIds)) {
                    $matchedTransferIds[$giftsKey] = $transfer->getKey();
                }
            }
        }

        $matched = [];
        $transferIds = array_values(array_unique($matchedTransferIds));
        if ($transferIds !== []) {
            $transfers = $this->transfer->newQuery()
                ->with(['deposit', 'withdraw.wallet'])
                ->whereIn($this->transfer->getKeyName(), $transferIds)
                ->get();

            foreach ($transfers as $transfer) {
                $matched[$transfer->getKey()] = $transfer;
            }
        }

        $result = [];
        foreach ($queryMap as $index => $item) {
            $key = $item['includeGifts'] ? $item['gifts'] : $item['strict'];
            $transferId = $matchedTransferIds[$key] ?? null;
            $result[$index] = is_int($transferId) ? ($matched[$transferId] ?? null) : null;
        }

        return $result;
    }

    public function one(PurchaseQueryInterface $query): ?Transfer
    {
        $result = $this->apply([$query]);

        return $result[0] ?? null;
    }

    private function key(int $fromId, int $toId, bool $includeGifts): string
    {
        return $fromId.':'.$toId.':'.($includeGifts ? '1' : '0');
    }
}
