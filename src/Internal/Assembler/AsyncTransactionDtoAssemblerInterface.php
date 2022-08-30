<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

interface AsyncTransactionDtoAssemblerInterface
{
    /**
     * @param null|array<mixed> $meta
     */
    public function create(
        string $uuid,
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta
    ): TransactionDtoInterface;
}
