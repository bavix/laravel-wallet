<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;

interface AsyncPrepareServiceInterface
{
    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     */
    public function deposit(
        string $uuid,
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     */
    public function withdraw(
        string $uuid,
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface;
}
