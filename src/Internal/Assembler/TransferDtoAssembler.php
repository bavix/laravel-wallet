<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    public function __construct(
        private AsyncTransferDtoAssemblerInterface $asyncTransferDtoAssembler,
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
        string $fee
    ): TransferDtoInterface {
        return $this->asyncTransferDtoAssembler->create(
            $this->uuidService->uuid4(),
            $depositId,
            $withdrawId,
            $status,
            $fromModel,
            $toModel,
            $discount,
            $fee
        );
    }
}
