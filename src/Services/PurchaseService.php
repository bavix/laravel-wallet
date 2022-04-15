<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;

final class PurchaseService implements PurchaseServiceInterface
{
    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array
    {
        $status = $gifts
            ? [Transfer::STATUS_PAID, Transfer::STATUS_GIFT]
            : [Transfer::STATUS_PAID];

        $arrays = [];
        $query = $customer->transfers();
        foreach ($basketDto->items() as $itemDto) {
            /** @var Model $product */
            $product = $itemDto->product();

            /**
             * As part of my work, "with" was added, it gives a 50x boost for a huge number of returns. In this case,
             * it's a crutch. It is necessary to come up with a more correct implementation of the internal and external
             * interface for "purchases".
             */
            $arrays[] = (clone $query)
                ->with(['deposit', 'withdraw.wallet'])
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
