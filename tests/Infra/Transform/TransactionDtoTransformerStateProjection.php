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
        $finalBalance = null;
        $checksum = null;
        if ($dto->getMeta() !== null) {
            $finalBalance = $dto->getMeta()['final_balance'] ?? null;
            $checksum = $dto->getMeta()['checksum'] ?? null;
        }

        return array_merge($this->transactionDtoTransformer->extract($dto), [
            'final_balance' => $finalBalance,
            'checksum' => $checksum,
        ]);
    }
}
