<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Assembler;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionDtoAssemblerStateAware implements TransactionDtoAssemblerInterface
{
    public function __construct(
        private TransactionDtoAssembler $transactionDtoAssembler,
    ) {
    }

    public function create(
        Model $payable,
        int $walletId,
        TransactionType $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta,
        ?string $uuid
    ): TransactionDtoInterface {
        $dto = $this->transactionDtoAssembler->create(
            $payable,
            $walletId,
            $type,
            $amount,
            $confirmed,
            $meta,
            $uuid
        );

        return new StateAwareTransactionDto($dto, '0', '0');
    }
}
