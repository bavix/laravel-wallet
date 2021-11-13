<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Service\JsonService;

final class TransactionDtoTransformer implements TransactionDtoTransformerInterface
{
    private JsonService $jsonService;

    public function __construct(JsonService $jsonService)
    {
        $this->jsonService = $jsonService;
    }

    public function extract(TransactionDto $dto): array
    {
        return [
            'uuid' => $dto->getUuid(),
            'payable_type' => $dto->getPayableType(),
            'payable_id' => $dto->getPayableId(),
            'wallet_id' => $dto->getWalletId(),
            'type' => $dto->getType(),
            'amount' => $dto->getAmount(),
            'confirmed' => $dto->isConfirmed(),
            'meta' => $this->jsonService->encode($dto->getMeta()),
            'created_at' => $dto->getCreatedAt(),
            'updated_at' => $dto->getUpdatedAt(),
        ];
    }
}
