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
    public function already(BasketDto $basketDto, Customer $customer, bool $gifts = false): array
    {
        $status = [Transfer::STATUS_PAID];
        if ($gifts) {
            $status[] = Transfer::STATUS_GIFT;
        }

        /** @var HasWallet $customer */
        /** @var Transfer $query */
        $result = [];
        $query = $customer->transfers();
        foreach ($basketDto->items() as $product) {
            /** @var Model $item */
            $item = $product->product();
            $result[] = (clone $query)
                ->where('to_type', $item->getMorphClass())
                ->where('to_id', $item->getKey())
                ->whereIn('status', $status)
                ->orderBy('id', 'desc')
                ->limit(count($product))
                ->get()
                ->all()
            ;
        }

        return array_merge(...$result);
    }
}
