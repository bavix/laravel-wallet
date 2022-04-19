<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\OptionDto;
use Bavix\Wallet\Internal\Dto\OptionDtoInterface;

final class OptionDtoAssembler implements OptionDtoAssemblerInterface
{
    public function create(array|null $data): OptionDtoInterface
    {
        return new OptionDto($data);
    }
}
