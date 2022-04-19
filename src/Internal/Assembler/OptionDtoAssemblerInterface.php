<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\OptionDtoInterface;

interface OptionDtoAssemblerInterface
{
    public function create(array|null $data): OptionDtoInterface;
}
