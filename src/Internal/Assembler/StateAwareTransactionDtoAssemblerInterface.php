<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\StateAwareTransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;

interface StateAwareTransactionDtoAssemblerInterface
{
    public function create(
        TransactionDtoInterface $dto,
        string $balanceBefore,
        string $balanceAfter,
    ): StateAwareTransactionDtoInterface;
}
