<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Cknow\Money\Money;

/**
 * Class Transaction.
 *
 * @property Money $currency
 */
class TransactionMoney extends \Bavix\Wallet\Models\Transaction
{
    protected ?\Cknow\Money\Money $currency = null;

    public function getCurrencyAttribute(): Money
    {
        if (!$this->currency) {
            $this->currency = \money($this->amount, $this->meta['currency'] ?? 'USD');
        }

        return $this->currency;
    }
}
