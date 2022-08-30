<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Illuminate\Database\Eloquent\Model;

final class AsyncTransferDtoAssembler implements AsyncTransferDtoAssemblerInterface
{
    public function create(
        string $uuid,
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee
    ): TransferDtoInterface {
        return new TransferDto(
            $uuid,
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
