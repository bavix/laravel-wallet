<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Dto;

use Bavix\Wallet\External\Contracts\CostDtoInterface;

final class Cost implements CostDtoInterface
{
    public function __construct(
        private int|float|string $value,
        private ?string $currency
    ) {
    }

    public function getValue(): string
    {
        return (string) $this->value;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
