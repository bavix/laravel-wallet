<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;

interface TransferDtoTransformerInterface
{
    public function extract(TransferDtoInterface $dto): array;
}
