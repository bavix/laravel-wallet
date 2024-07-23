<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\External\Contracts\OptionDtoInterface;

interface OptionDtoAssemblerInterface
{
    /**
     * Create an OptionDto object from the given data.
     *
     * @param array<mixed>|null $data The data to create the OptionDto from.
     *                        This can be null, in which case an empty
     *                        OptionDto object will be created.
     * @return OptionDtoInterface The created OptionDto object.
     */
    public function create(array|null $data): OptionDtoInterface;
}
