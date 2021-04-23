<?php

declare(strict_types=1);

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\RateService;
use Bavix\Wallet\Services\WalletService;

class Rate implements Rateable
{
    protected RateService $rateService;

    protected WalletService $walletService;

    protected string $amount;

    protected string $currency;

    public function __construct(
        RateService $rateService,
        WalletService $walletService
    ) {
        $this->rateService = $rateService;
        $this->walletService = $walletService;
    }

    public function withAmount($amount): Rateable
    {
        $this->amount = (string) $amount;

        return $this;
    }

    public function withCurrency(Wallet $wallet): Rateable
    {
        $model = $this->walletService->getWallet($wallet);
        $this->currency = $model->getCurrencyAttribute();

        return $this;
    }

    public function convertTo(Wallet $wallet): string
    {
        $model = $this->walletService->getWallet($wallet);

        return $this->rateService->convertTo(
            $this->currency,
            $model->currency,
            $this->amount
        );
    }
}
