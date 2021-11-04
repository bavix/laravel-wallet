<?php

namespace Bavix\Wallet\Test\Objects;

use Bavix\Wallet\Internal\Dto\TransactionDto;

class TransactionDtoTransformer extends \Bavix\Wallet\Internal\Transform\TransactionDtoTransformer
{
    public function extract(TransactionDto $dto): array
    {
        $bankMethod = null;
        if ($dto->getMeta() !== null) {
            $bankMethod = $dto->getMeta()['bank_method'] ?? null;
        }

        return array_merge(parent::extract($dto), [
            'bank_method' => $bankMethod,
        ]);
    }
}
