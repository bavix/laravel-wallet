<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\CostDtoInterface;

interface CostDtoAssemblerInterface
{
    public function create(CostDtoInterface|int|float|string $data): CostDtoInterface;
}
