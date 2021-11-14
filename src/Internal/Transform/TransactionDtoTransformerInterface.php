<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;

interface TransactionDtoTransformerInterface
{
    public function extract(TransactionDtoInterface $dto): array;
}
