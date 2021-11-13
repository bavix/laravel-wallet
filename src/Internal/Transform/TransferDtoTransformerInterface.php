<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransferDto;

interface TransferDtoTransformerInterface
{
    public function extract(TransferDto $dto): array;
}
