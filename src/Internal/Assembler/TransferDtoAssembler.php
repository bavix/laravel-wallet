<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\UuidInterface;
use Illuminate\Database\Eloquent\Model;

class TransferDtoAssembler
{
    private UuidInterface $uuidService;

    public function __construct(UuidInterface $uuidService)
    {
        $this->uuidService = $uuidService;
    }

    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        int $fee
    ): TransferDto {
        return new TransferDto(
            $this->uuidService->uuid4(),
            $depositId,
            $withdrawId,
            $status,
            $fromModel->getMorphClass(),
            $fromModel->getKey(),
            $toModel->getMorphClass(),
            $toModel->getKey(),
            $discount,
            $fee
        );
    }
}
