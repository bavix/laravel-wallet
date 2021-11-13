<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransactionDto;

interface TransactionDtoTransformerInterface
{
    public function extract(TransactionDto $dto): array;
}
