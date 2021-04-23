<?php

declare(strict_types=1);

namespace Bavix\Wallet\Assemblers;

use Bavix\Wallet\Dto\TransactionDto;
use Bavix\Wallet\Services\UuidFactoryService;

class TransactionDtoAssembler
{
    private UuidFactoryService $uuidFactoryService;

    public function __construct(UuidFactoryService $uuidFactoryService)
    {
        $this->uuidFactoryService = $uuidFactoryService;
    }

    public function create(
        string $payable_type,
        string $payable_id,
        string $type,
        int $walletId,
        bool $confirmed,
        int $amount,
        ?array $meta
    ): TransactionDto {
        return new TransactionDto(
            $type,
            $walletId,
            $this->uuidFactoryService->uuid4(),
            $confirmed,
            $amount,
            $meta
        );
    }
}
