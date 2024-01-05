<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    public function __construct(
        private UuidFactoryServiceInterface $uuidService
    ) {
    }

    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee,
        ?string $uuid
    ): TransferDtoInterface {
        return new TransferDto(
            $uuid ?? $this->uuidService->uuid4(),
            $depositId,
            $withdrawId,
            $status,
            $fromModel->getKey(),
            $toModel->getKey(),
            $discount,
            $fee
        );
    }
}
