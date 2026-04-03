<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final readonly class TransactionDtoTransformerStateProjection implements TransactionDtoTransformerInterface
{
    public function __construct(
        private TransactionDtoTransformer $transactionDtoTransformer
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $balanceAfter = null;
        $stateHash = null;
        if ($dto->getMeta() !== null) {
            $balanceAfter = $dto->getMeta()['balance_after'] ?? null;
            $stateHash = $dto->getMeta()['state_hash'] ?? null;
        }

        return array_merge($this->transactionDtoTransformer->extract($dto), [
            'balance_after' => $balanceAfter,
            'state_hash' => $stateHash,
        ]);
    }
}
