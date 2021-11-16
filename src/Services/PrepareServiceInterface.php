<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;

interface PrepareServiceInterface
{
    /**
     * @throws AmountInvalid
     */
    public function deposit(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDtoInterface;

    /**
     * @throws AmountInvalid
     */
    public function withdraw(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDtoInterface;

    /**
     * @param float|int|string $amount
     *
     * @throws AmountInvalid
     */
    public function transferLazy(Wallet $from, Wallet $to, string $status, $amount, ?array $meta = null): TransferLazyDtoInterface;
}
