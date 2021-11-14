<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final class TransactionDtoTransformerCustom implements TransactionDtoTransformerInterface
{
    private TransactionDtoTransformer $transactionDtoTransformer;

    public function __construct(TransactionDtoTransformer $transactionDtoTransformer)
    {
        $this->transactionDtoTransformer = $transactionDtoTransformer;
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $bankMethod = null;
        if ($dto->getMeta() !== null) {
            $bankMethod = $dto->getMeta()['bank_method'] ?? null;
        }

        return array_merge($this->transactionDtoTransformer->extract($dto), [
            'bank_method' => $bankMethod,
        ]);
    }
}
