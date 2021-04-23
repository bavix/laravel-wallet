<?php

declare(strict_types=1);

namespace Bavix\Wallet\Assemblers;

use Bavix\Wallet\Dto\TransactionDto;
use Bavix\Wallet\Dto\TransferDto;
use Bavix\Wallet\Services\UuidFactoryService;

class TransferDtoAssembler
{
    private UuidFactoryService $uuidFactoryService;

    public function __construct(UuidFactoryService $uuidFactoryService)
    {
        $this->uuidFactoryService = $uuidFactoryService;
    }

    public function create(
        TransactionDto $fromDto,
        TransactionDto $toDto,
        ?array $meta
    ): TransferDto {
        return new TransferDto(
            $this->uuidFactoryService->uuid4(),
            $fromDto,
            $toDto,
            $meta
        );
    }
}
