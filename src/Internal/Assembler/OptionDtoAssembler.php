<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\OptionDtoInterface;
use Bavix\Wallet\External\Dto\Option;

final class OptionDtoAssembler implements OptionDtoAssemblerInterface
{
    public function create(array|null $data): OptionDtoInterface
    {
        return new Option($data);
    }
}
