<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\AtmService;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Models\Transaction;
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
        $wallet = app(WalletService::class)->getWallet($this);

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
        /** @var Wallet $from */
        $from = app(WalletService::class)->getWallet($this);

        return app(LockService::class)->lock($this, __FUNCTION__, static function () use ($from, $to, $amount, $meta) {
            return app(DbService::class)->transaction(static function () use ($from, $to, $amount, $meta) {
                $walletService = app(WalletService::class);
                $math = app(MathInterface::class);
                $fee = $walletService->fee($to, $amount);
                $rate = app(ExchangeInterface::class)->convertTo(
                    $walletService->getWallet($from)->currency,
                    $walletService->getWallet($to)->currency,
                    1
                );

                $withdraw = app(CommonService::class)
                    ->makeOperation($from, Transaction::TYPE_WITHDRAW, $math->add($amount, $fee), $meta)
                ;

                $deposit = app(CommonService::class)
                    ->makeOperation($to, Transaction::TYPE_DEPOSIT, $math->floor($math->mul($amount, $rate, 1)), $meta)
                ;

                $castService = app(CastService::class);

                $transfer = app(TransferDtoAssembler::class)->create(
                    $deposit->getKey(),
                    $withdraw->getKey(),
                    Transfer::STATUS_EXCHANGE,
                    $castService->getModel($from),
                    $castService->getModel($to),
                    0,
                    $fee
                );

                $transfers = app(AtmService::class)->makeTransfers([$transfer]);

                return current($transfers);
            });
        });
    }
}
