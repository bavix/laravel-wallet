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
        ['queryMap' => $queryMap, 'fromIds' => $fromIds, 'toIds' => $toIds] = $this->buildQueryMap($objects);

        $matchedTransferIds = $this->matchByPurchases($fromIds, $toIds);
        $needFallback = $this->collectFallbackKeys($queryMap, $matchedTransferIds);

        if ($needFallback !== []) {
            $matchedTransferIds = $this->matchByTransfersFallback($fromIds, $toIds, $needFallback, $matchedTransferIds);
        }

        $matchedTransfers = $this->loadMatchedTransfers($matchedTransferIds);

        return $this->buildResult($queryMap, $matchedTransferIds, $matchedTransfers);
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

    /**
     * @param array<int, PurchaseQueryInterface> $objects
     * @return array{
     *     queryMap: array<int, array{strict: string, gifts: string, includeGifts: bool}>,
     *     fromIds: array<int, true>,
     *     toIds: array<int, true>
     * }
     */
    private function buildQueryMap(array $objects): array
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

        return [
            'queryMap' => $queryMap,
            'fromIds' => $fromIds,
            'toIds' => $toIds,
        ];
    }

    /**
     * @param array<int, true> $fromIds
     * @param array<int, true> $toIds
     * @return array<string, int>
     */
    private function matchByPurchases(array $fromIds, array $toIds): array
    {
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

        return $matchedTransferIds;
    }

    /**
     * @param array<int, array{strict: string, gifts: string, includeGifts: bool}> $queryMap
     * @param array<string, int> $matchedTransferIds
     * @return array<string, true>
     */
    private function collectFallbackKeys(array $queryMap, array $matchedTransferIds): array
    {
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

        return $needFallback;
    }

    /**
     * @param array<int, true> $fromIds
     * @param array<int, true> $toIds
     * @param array<string, true> $needFallback
     * @param array<string, int> $matchedTransferIds
     * @return array<string, int>
     */
    private function matchByTransfersFallback(
        array $fromIds,
        array $toIds,
        array $needFallback,
        array $matchedTransferIds
    ): array {
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

        return $matchedTransferIds;
    }

    /**
     * @param array<string, int> $matchedTransferIds
     * @return array<int, Transfer>
     */
    private function loadMatchedTransfers(array $matchedTransferIds): array
    {
        $matched = [];
        $transferIds = array_values(array_unique($matchedTransferIds));
        if ($transferIds === []) {
            return $matched;
        }

        $transfers = $this->transfer->newQuery()
            ->with(['deposit', 'withdraw.wallet'])
            ->whereIn($this->transfer->getKeyName(), $transferIds)
            ->get();

        foreach ($transfers as $transfer) {
            $matched[$transfer->getKey()] = $transfer;
        }

        return $matched;
    }

    /**
     * @param array<int, array{strict: string, gifts: string, includeGifts: bool}> $queryMap
     * @param array<string, int> $matchedTransferIds
     * @param array<int, Transfer> $matchedTransfers
     * @return array<int, Transfer|null>
     */
    private function buildResult(array $queryMap, array $matchedTransferIds, array $matchedTransfers): array
    {
        $result = [];
        foreach ($queryMap as $index => $item) {
            $key = $item['includeGifts'] ? $item['gifts'] : $item['strict'];
            $transferId = $matchedTransferIds[$key] ?? null;
            $result[$index] = is_int($transferId) ? ($matchedTransfers[$transferId] ?? null) : null;
        }

        return $result;
    }
}
