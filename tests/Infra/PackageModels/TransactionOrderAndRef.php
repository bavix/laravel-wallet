<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

/**
 * Class Transaction.
 *
 * @property null|string $order_id
 * @property null|string $ref_id
 */
final class TransactionOrderAndRef extends \Bavix\Wallet\Models\Transaction
{
    public function getFillable(): array
    {
        return array_merge($this->fillable, ['order_id', 'ref_id']);
    }
}
