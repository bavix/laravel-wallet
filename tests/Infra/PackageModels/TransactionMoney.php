<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Cknow\Money\Money;

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
        $this->currency ??= \money($this->amount, $this->meta['currency'] ?? 'USD');

        return $this->currency;
    }
}
