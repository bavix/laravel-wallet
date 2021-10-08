<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\PurchaseInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

class PurchaseService implements PurchaseInterface
{
    public function already(Customer $customer, BasketDto $basketDto, bool $gifts = false): array
    {
        $status = $gifts
            ? [Transfer::STATUS_PAID, Transfer::STATUS_GIFT]
            : [Transfer::STATUS_PAID];

        /** @var HasWallet $customer */
        /** @var Transfer $query */
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
