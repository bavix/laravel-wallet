<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;
use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Internal\Service\PrepareService;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\WalletService;

trait CanExchange
{
    /**
     * {@inheritdoc}
     */
    public function exchange(Wallet $to, $amount, ?array $meta = null): Transfer
    {
        $wallet = app(CastService::class)->getWallet($this);

        app(ConsistencyInterface::class)->checkPotential($wallet, $amount);

        return $this->forceExchange($to, $amount, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function safeExchange(Wallet $to, $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forceExchange(Wallet $to, $amount, ?array $meta = null): Transfer
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($to, $amount, $meta) {
            return app(DbService::class)->transaction(function () use ($to, $amount, $meta) {
                $prepareService = app(PrepareService::class);
                $mathService = app(MathInterface::class);
                $castService = app(CastService::class);
                $fee = app(WalletService::class)->fee($to, $amount);
                $rate = app(ExchangeInterface::class)->convertTo(
                    $castService->getWallet($this)->currency,
                    $castService->getWallet($to)->currency,
                    1
                );

                $withdrawDto = $prepareService->withdraw($this, $mathService->add($amount, $fee), $meta);
                $depositDto = $prepareService->deposit($to, $mathService->floor($mathService->mul($amount, $rate, 1)), $meta);

                $transferLazyDto = new TransferLazyDto(
                    $this,
                    $to,
                    0,
                    $fee,
                    $withdrawDto,
                    $depositDto,
                    Transfer::STATUS_EXCHANGE,
                );

                $transfers = app(CommonService::class)->applyTransfers([$transferLazyDto]);

                return current($transfers);
            });
        });
    }
}
