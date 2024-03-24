<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final readonly class TransactionOrderAndRefDtoTransformerDecorator implements TransactionDtoTransformerInterface
{
    public function __construct(
        private TransactionDtoTransformer $transactionDtoTransformer
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        return array_merge($this->transactionDtoTransformer->extract($dto), [
            'order_id' => $dto->getMeta()['order_id'] ?? null,
            'ref_id' => $dto->getMeta()['ref_id'] ?? null,
        ]);
    }
}
