<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

interface BookkeeperServiceInterface
{
    public function missing(Wallet $wallet): bool;

    public function amount(Wallet $wallet): string;

    /**
     * @param float|int|string $value
     */
    public function sync(Wallet $wallet, $value): bool;

    /**
     * @param float|int|string $value
     */
    public function increase(Wallet $wallet, $value): string;
}
