<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;

interface PrepareServiceInterface
{
    public function deposit(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDto;

    public function withdraw(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDto;

    /**
     * @param float|int|string $amount
     */
    public function transferLazy(Wallet $from, Wallet $to, string $status, $amount, ?array $meta = null): TransferLazyDto;
}
