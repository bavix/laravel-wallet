<?php

namespace Bavix\Wallet\Test\Common\Models;

use Cknow\Money\Money;

/**
 * Class Transaction.
 *
 * @property Money $currency
 */
class TransactionMoney extends \Bavix\Wallet\Models\Transaction
{
    private ?Money $currency = null;

    public function getCurrencyAttribute(): Money
    {
        if ($this->currency === null) {
            $this->currency = \money($this->amount, $this->meta['currency'] ?? 'USD');
        }

        return $this->currency;
    }
}
