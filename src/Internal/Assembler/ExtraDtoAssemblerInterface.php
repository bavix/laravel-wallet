<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;

interface ExtraDtoAssemblerInterface
{
    /**
     * Create ExtraDto.
     *
     * @param ExtraDtoInterface|array<mixed>|null $data
     *     The data to create ExtraDto from. Can be either ExtraDtoInterface object, array or null.
     * @return ExtraDtoInterface
     *     The created ExtraDto.
     */
    public function create(ExtraDtoInterface|array|null $data): ExtraDtoInterface;
}
