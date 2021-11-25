<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;

final class TransferDtoTransformer implements TransferDtoTransformerInterface
{
    public function extract(TransferDtoInterface $dto): array
    {
        return [
            'uuid' => $dto->getUuid(),
            'deposit_id' => $dto->getDepositId(),
            'withdraw_id' => $dto->getWithdrawId(),
            'status' => $dto->getStatus(),
            'from_type' => $dto->getFromType(),
            'from_id' => $dto->getFromId(),
            'to_type' => $dto->getToType(),
            'to_id' => $dto->getToId(),
            'discount' => $dto->getDiscount(),
            'fee' => $dto->getFee(),
            'created_at' => $dto->getCreatedAt(),
            'updated_at' => $dto->getUpdatedAt(),
        ];
    }
}
