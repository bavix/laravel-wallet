<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

interface TaxServiceInterface
{
    /**
     * @param float|int|string $amount
     */
    public function getFee(Wallet $wallet, $amount): string;
}
