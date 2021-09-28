<?php

namespace Bavix\Wallet\Test\Common\Models;

/**
 * Class Transaction.
 *
 * @property null|string $bank_method
 */
class Transaction extends \Bavix\Wallet\Models\Transaction
{
    /**
     * {@inheritdoc}
     */
    public function getFillable(): array
    {
        return array_merge($this->fillable, [
            'bank_method',
        ]);
    }
}
