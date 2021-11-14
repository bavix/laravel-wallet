<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\MathServiceInterface;

final class TaxService implements TaxServiceInterface
{
    private MathServiceInterface $mathService;
    private CastServiceInterface $castService;

    public function __construct(
        MathServiceInterface $mathService,
        CastServiceInterface $castService
    ) {
        $this->mathService = $mathService;
        $this->castService = $castService;
    }

    /**
     * @param float|int|string $amount
     */
    public function getFee(Wallet $wallet, $amount): string
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $this->castService->getWallet($wallet)->decimal_places
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

        return (string) $fee;
    }
}
