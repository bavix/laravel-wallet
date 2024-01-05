<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Bavix\Wallet\Test\Infra\Values\Money;

/**
 * Class Transaction.
 *
 * @property Money $currency
 */
final class TransactionMoney extends \Bavix\Wallet\Models\Transaction
{
    private ?Money $currency = null;

    public function getCurrencyAttribute(): Money
    {
        $this->currency ??= new Money($this->amount, $this->meta['currency'] ?? 'USD');

        return $this->currency;
    }
}
