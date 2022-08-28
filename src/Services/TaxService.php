<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\MaximalTaxable;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\MathServiceInterface;

/**
 * @internal
 */
final class TaxService implements TaxServiceInterface
{
    public function __construct(
        private MathServiceInterface $mathService,
        private CastServiceInterface $castService
    ) {
    }

    public function getFee(Wallet $wallet, float|int|string $amount): string
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $this->castService->getWallet($wallet)
                        ->decimal_places
                )
            );
        }

        /**
         * Added minimum commission condition.
         *
         * @see https://github.com/bavix/laravel-wallet/issues/64#issuecomment-514483143
         */
        if ($wallet instanceof MinimalTaxable) {
            $minimal = $wallet->getMinimalFee();
            if ($this->mathService->compare($fee, $minimal) === -1) {
                $fee = $minimal;
            }
        }

        if ($wallet instanceof MaximalTaxable) {
            $maximal = $wallet->getMaximalFee();
            if ($this->mathService->compare($maximal, $fee) === -1) {
                $fee = $maximal;
            }
        }

        return (string) $fee;
    }
}
