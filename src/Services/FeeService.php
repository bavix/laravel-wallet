<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\Wallet;

class FeeService
{
    private FloatService $floatService;

    private MathInterface $mathService;

    public function __construct(
        FloatService $floatService,
        MathInterface $mathService
    ) {
        $this->floatService = $floatService;
        $this->mathService = $mathService;
    }

    public function fee(Wallet $wallet, $amount)
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $placesValue = $this->floatService->exponent($wallet);
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $placesValue
                )
            );
        }

        /*
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

        return $fee;
    }
}
