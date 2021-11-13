<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Objects;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final class TransactionDtoTransformerCustom implements TransactionDtoTransformerInterface
{
    private TransactionDtoTransformer $transactionDtoTransformer;

    public function __construct(TransactionDtoTransformer $transactionDtoTransformer)
    {
        $this->transactionDtoTransformer = $transactionDtoTransformer;
    }

    public function extract(TransactionDto $dto): array
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
