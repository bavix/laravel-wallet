<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\CostDtoInterface;
use Bavix\Wallet\External\Dto\Cost;

final class CostDtoAssembler implements CostDtoAssemblerInterface
{
    public function create(CostDtoInterface|int|float|string $data): CostDtoInterface
    {
        if ($data instanceof CostDtoInterface) {
            return $data;
        }

        return new Cost($data, null);
    }
}
