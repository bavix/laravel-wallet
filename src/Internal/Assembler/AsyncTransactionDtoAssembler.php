<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

final class AsyncTransactionDtoAssembler implements AsyncTransactionDtoAssemblerInterface
{
    public function create(
        string $uuid,
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta
    ): TransactionDtoInterface {
        return new TransactionDto(
            $uuid,
            $payable->getMorphClass(),
            $payable->getKey(),
            $walletId,
            $type,
            $amount,
            $confirmed,
            $meta
        );
    }
}
