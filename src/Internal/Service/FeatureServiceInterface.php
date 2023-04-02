<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface FeatureServiceInterface
{
    /**
     * The mode prohibits work between wallets 1:1 for different currencies.
     */
    public function isCurrencyStrictMode(): bool;
}
