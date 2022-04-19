<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;

interface ExtraDtoAssemblerInterface
{
    public function create(ExtraDtoInterface|array|null $data): ExtraDtoInterface;
}
