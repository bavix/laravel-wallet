<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\OptionDtoInterface;

interface OptionDtoAssemblerInterface
{
    /**
     * @param null|array<mixed> $data
     */
    public function create(array|null $data): OptionDtoInterface;
}
