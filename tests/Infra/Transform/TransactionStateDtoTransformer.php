<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Transform;

use Bavix\Wallet\Internal\Dto\StateAwareTransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final class TransactionStateDtoTransformer implements TransactionDtoTransformerInterface
{
    public function __construct(
        private readonly TransactionDtoTransformer $transactionDtoTransformer,
        private readonly MathServiceInterface $mathService,
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $result = $this->transactionDtoTransformer->extract($dto);

        if (! $dto instanceof StateAwareTransactionDtoInterface) {
            return $result;
        }

        $result['balance_before'] = $dto->getBalanceBefore();
        $result['balance_after'] = $dto->getBalanceAfter();
        $amount = $this->mathService->round($dto->getAmount());
        $result['state_hash'] = hash(
            'sha256',
            $dto->getUuid().':'.$amount.':'.$dto->getBalanceBefore().':'.$dto->getBalanceAfter()
        );

        return $result;
    }
}
