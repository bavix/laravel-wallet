<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

interface TransactionDtoAssemblerInterface
{
    public function create(
        Model $payable,
        int $walletId,
        string $type,
        string $amount,
        bool $confirmed,
        ?array $meta
    ): TransactionDtoInterface;
}
