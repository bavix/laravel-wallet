<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\UuidInterface;
use Illuminate\Database\Eloquent\Model;

final class TransactionDtoAssembler
{
    private UuidInterface $uuidService;

    public function __construct(UuidInterface $uuidService)
    {
        $this->uuidService = $uuidService;
    }

    public function create(
        Model $payable,
        int $walletId,
        string $type,
        string $amount,
        bool $confirmed,
        ?array $meta
    ): TransactionDto {
        return new TransactionDto(
            $this->uuidService->uuid4(),
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
