<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductCurrencyStrictInterface
{
    /**
     * @return string[]
     */
    public function getAllowedCurrenciesAttribute(): array;
}
