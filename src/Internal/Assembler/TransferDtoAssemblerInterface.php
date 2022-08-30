<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @see AsyncTransferDtoAssemblerInterface
 */
interface TransferDtoAssemblerInterface
{
    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee
    ): TransferDtoInterface;
}
