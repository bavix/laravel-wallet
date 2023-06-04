<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

/**
 * @internal
 */
final class FeatureService implements FeatureServiceInterface
{
    private bool $value;

    public function __construct()
    {
        $this->value = (bool) config('wallet.features.currency_strict', false);
    }

    public function isCurrencyStrictMode(): bool
    {
        return $this->value;
    }
}
