<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    private UuidFactoryServiceInterface $uuidService;

    public function __construct(UuidFactoryServiceInterface $uuidService)
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
        string $fee
    ): TransferDtoInterface {
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
