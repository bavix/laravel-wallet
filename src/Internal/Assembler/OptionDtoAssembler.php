<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\OptionDtoInterface;
use Bavix\Wallet\Internal\Dto\OptionDto;

final class OptionDtoAssembler implements OptionDtoAssemblerInterface
{
    public function create(array|null $data): OptionDtoInterface
    {
        return new OptionDto($data);
    }
}
