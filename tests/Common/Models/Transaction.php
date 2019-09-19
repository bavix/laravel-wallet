<?php

namespace Bavix\Wallet\Test\Common\Models;

/**
 * Class Transaction
 * @package Bavix\Wallet\Test\Common\Models
 * @property null|string $bank_method
 */
class Transaction extends \Bavix\Wallet\Models\Transaction
{

    /**
     * @inheritDoc
     */
    public function getFillable(): array
    {
        return array_merge($this->fillable, [
            'bank_method',
        ]);
    }

}
