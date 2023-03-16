<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface TaxServiceInterface
{
    public function getFee(Wallet $wallet, float|int|string $amount): string;
}
