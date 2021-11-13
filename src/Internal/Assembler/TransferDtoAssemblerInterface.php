<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Illuminate\Database\Eloquent\Model;

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
    ): TransferDto;
}
