<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Contracts;

interface CostDtoInterface
{
    public function getCurrency(): ?string;

    public function getValue(): string;
}
