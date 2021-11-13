<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\CustomerInterface;
use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\PurchaseInterface;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;

final class PurchaseService implements PurchaseInterface
{
    public function already(CustomerInterface $customer, BasketDto $basketDto, bool $gifts = false): array
    {
        $status = $gifts
            ? [Transfer::STATUS_PAID, Transfer::STATUS_GIFT]
            : [Transfer::STATUS_PAID];

        $arrays = [];
        $query = $customer->transfers();
        foreach ($basketDto->items() as $itemDto) {
            /** @var Model $product */
            $product = $itemDto->product();
            $arrays[] = (clone $query)
                ->where('to_type', $product->getMorphClass())
                ->where('to_id', $product->getKey())
                ->whereIn('status', $status)
                ->orderBy('id', 'desc')
                ->limit(count($itemDto))
                ->get()
                ->all()
            ;
        }

        return array_merge(...$arrays);
    }
}
