<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Illuminate\Database\Eloquent\Model;

interface TransferDtoAssemblerInterface
{
/**
     * Create transfer dto.
     *
     * @param int $depositId ID of deposit transaction
     * @param int $withdrawId ID of withdraw transaction
     * @param string $status Status of transfer
     * @param Model $fromModel From wallet model
     * @param Model $toModel To wallet model
     * @param int $discount Discount of transfer
     * @param string $fee Fee of transfer
     * @param string|null $uuid UUID of transfer
     * @param array<mixed>|null $extra Extra data of transfer
     * @return TransferDtoInterface
     */
    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee,
        ?string $uuid,
        ?array $extra,
    ): TransferDtoInterface;
}
