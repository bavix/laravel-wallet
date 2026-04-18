<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\StateAwareTransactionDto;
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;

final class StateAwareTransactionDtoAssembler implements StateAwareTransactionDtoAssemblerInterface
{
    public function create(
        TransactionDtoInterface $dto,
        string $balanceBefore,
        string $balanceAfter,
    ): StateAwareTransactionDtoInterface {
        return new StateAwareTransactionDto($dto, $balanceBefore, $balanceAfter);
    }
}
