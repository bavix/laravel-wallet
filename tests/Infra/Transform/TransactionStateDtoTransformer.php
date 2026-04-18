<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\TransactionStateServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final readonly class TransactionStateDtoTransformer implements TransactionDtoTransformerInterface
{
    public function __construct(
        private TransactionDtoTransformer $transactionDtoTransformer,
        private TransactionStateServiceInterface $transactionStateService,
        private MathServiceInterface $mathService,
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $result = $this->transactionDtoTransformer->extract($dto);

        if (! $this->transactionStateService->has($dto->getUuid())) {
            return $result;
        }

        $before = $this->transactionStateService->before($dto->getUuid());
        $after = $this->transactionStateService->after($dto->getUuid());

        $result['balance_before'] = $before['balance'];
        $result['balance_after'] = $after['balance'];
        $amount = $this->mathService->round($dto->getAmount());
        $result['state_hash'] = hash(
            'sha256',
            $dto->getUuid().':'.$amount.':'.$before['balance'].':'.$after['balance']
        );

        return $result;
    }
}
