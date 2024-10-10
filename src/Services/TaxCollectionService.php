<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\External\Enums\TransactionType;
use Bavix\Wallet\Interfaces\TaxConstraintsInterface;
use Bavix\Wallet\Interfaces\TaxInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\MathServiceInterface;

class TaxCollectionService implements TaxCollectionServiceInterface
{
    public function __construct(
        private MathServiceInterface $mathService,
        private CastServiceInterface $castService,
    ) {
    }

    public function calculate(
        TransactionType $type,
        Wallet $wallet, 
        float|int|string $amount,
    ): string {
        $feePercent = null;
        if ($wallet instanceof TaxInterface) {
            $feePercent = $wallet->getTaxPercent($type);
        }

        $feeMinimum = null;
        $feeMaximum = null;
        if ($wallet instanceof TaxConstraintsInterface) {
            $feeMinimum = $wallet->getMinimumTax($type);
            $feeMaximum = $wallet->getMaximumTax($type);
        }

        $fee = '0';
        if ($feePercent !== null) {
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $feePercent, 0),
                    100,
                    $this->castService->getWallet($wallet)
                        ->decimal_places
                )
            );
        }

        if ($feeMinimum !== null && $this->mathService->compare($fee, $feeMinimum) === -1) {
            $fee = $feeMinimum;
        }

        if ($feeMaximum !== null && $this->mathService->compare($feeMaximum, $fee) === -1) {
            $fee = $feeMaximum;
        }

        return $fee;
    }
}
