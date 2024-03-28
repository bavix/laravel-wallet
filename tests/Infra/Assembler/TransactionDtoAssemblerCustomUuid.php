<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Assembler;

use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionDtoAssemblerCustomUuid implements TransactionDtoAssemblerInterface
{
    public const UUID_FOR_TEST = '00000000-e26c-48af-8f61-284e37d3f18e';

    public function __construct(
        private TransactionDtoAssembler $assembler,
    ) {
    }

    public function create(
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta,
        ?string $uuid,
    ): TransactionDtoInterface {
        return $this->assembler->create(
            $payable,
            $walletId,
            $type,
            $amount,
            $confirmed,
            $meta,
            $uuid ?? $this->generate(),
        );
    }

    private function generate(): string
    {
        return self::UUID_FOR_TEST;
    }
}
