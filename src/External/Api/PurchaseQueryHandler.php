<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CastServiceInterface;

/**
 * @internal
 */
final readonly class PurchaseQueryHandler implements PurchaseQueryHandlerInterface
{
    public function __construct(
        private CastServiceInterface $castService,
        private Transfer $transfer
    ) {
    }

    public function apply(array $objects): array
    {
        $queryMap = [];
        $fromIds = [];
        $toIds = [];

        foreach ($objects as $index => $object) {
            $fromId = $this->castService->getWallet($object->getCustomer())->getKey();
            $toId = $this->castService->getWallet($object->getReceiving() ?? $object->getProduct())->getKey();

            $fromIds[$fromId] = true;
            $toIds[$toId] = true;

            $queryMap[$index] = [
                'strict' => $this->key($fromId, $toId, false),
                'gifts' => $this->key($fromId, $toId, true),
                'includeGifts' => $object->includeGifts(),
            ];
        }

        $matched = [];
        $transfers = $this->transfer->newQuery()
            ->with(['deposit', 'withdraw.wallet'])
            ->whereIn('from_id', array_keys($fromIds))
            ->whereIn('to_id', array_keys($toIds))
            ->whereIn('status', [TransferStatus::Paid->value, TransferStatus::Gift->value])
            ->orderByDesc('id')
            ->get();

        foreach ($transfers as $transfer) {
            $strictKey = $this->key($transfer->from_id, $transfer->to_id, false);
            if ($transfer->status === TransferStatus::Paid && ! array_key_exists($strictKey, $matched)) {
                $matched[$strictKey] = $transfer;
            }

            $giftsKey = $this->key($transfer->from_id, $transfer->to_id, true);
            if (! array_key_exists($giftsKey, $matched)) {
                $matched[$giftsKey] = $transfer;
            }
        }

        $result = [];
        foreach ($queryMap as $index => $item) {
            $key = $item['includeGifts'] ? $item['gifts'] : $item['strict'];
            $result[$index] = $matched[$key] ?? null;
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
