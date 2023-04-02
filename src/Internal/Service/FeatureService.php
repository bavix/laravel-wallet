<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

/**
 * @internal
 */
final class FeatureService implements FeatureServiceInterface
{
    public function isCurrencyStrictMode(): bool
    {
        /**
         * @var $value bool|int
         */
        $value = config('wallet.features.currency_strict', false);

        return (bool) $value;
    }
}
