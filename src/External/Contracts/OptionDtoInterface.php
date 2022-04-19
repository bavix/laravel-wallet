<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Contracts;

interface OptionDtoInterface
{
    public function getMeta(): ?array;

    public function isConfirmed(): bool;
}
