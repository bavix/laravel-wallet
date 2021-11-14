<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AtomicKeyServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\TaxServiceInterface;

trait CanExchange
{
    /**
     * {@inheritdoc}
     */
    public function exchange(Wallet $to, $amount, ?array $meta = null): Transfer
    {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        app(ConsistencyServiceInterface::class)->checkPotential($wallet, $amount);

        return $this->forceExchange($to, $amount, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function safeExchange(Wallet $to, $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (ExceptionInterface $throwable) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forceExchange(Wallet $to, $amount, ?array $meta = null): Transfer
    {
        $atomicKey = app(AtomicKeyServiceInterface::class)
            ->getIdentifier($this)
        ;

        return app(LockServiceInterface::class)->block($atomicKey, fn () => app(DatabaseServiceInterface::class)->transaction(function () use ($to, $amount, $meta) {
            $prepareService = app(PrepareServiceInterface::class);
            $mathService = app(MathServiceInterface::class);
            $castService = app(CastServiceInterface::class);
            $taxService = app(TaxServiceInterface::class);
            $fee = $taxService->getFee($to, $amount);
            $rate = app(ExchangeServiceInterface::class)->convertTo(
                $castService->getWallet($this)->currency,
                $castService->getWallet($to)->currency,
                1
            );

            $withdrawDto = $prepareService->withdraw($this, $mathService->add($amount, $fee), $meta);
            $depositDto = $prepareService->deposit($to, $mathService->floor($mathService->mul($amount, $rate, 1)), $meta);
            $transferLazyDto = app(TransferLazyDtoAssemblerInterface::class)->create(
                $this,
                $to,
                0,
                $fee,
                $withdrawDto,
                $depositDto,
                Transfer::STATUS_EXCHANGE,
            );

            $transfers = app(CommonServiceLegacy::class)->applyTransfers([$transferLazyDto]);

            return current($transfers);
        }));
    }
}
