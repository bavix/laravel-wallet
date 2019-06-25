<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Facades\DB;

trait CanExchange
{

    /**
     * @inheritDoc
     */
    public function exchange(Wallet $to, int $amount, ?array $meta = null): Transfer
    {
        $wallet = app(WalletService::class)
            ->getWallet($this);

        app(CommonService::class)
            ->verifyWithdraw($wallet, $amount);

        return $this->forceExchange($to, $amount, $meta);
    }

    /**
     * @inheritDoc
     */
    public function safeExchange(Wallet $to, int $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function forceExchange(Wallet $to, int $amount, ?array $meta = null): Transfer
    {
        /**
         * @var Wallet $from
         */
        $from = app(WalletService::class)->getWallet($this);

        return DB::transaction(function() use ($from, $to, $amount, $meta) {
            $rate = app(ExchangeService::class)->rate($from, $to);
            $fee = app(WalletService::class)->fee($to, $amount);
            $withdraw = $from->forceWithdraw($amount + $fee, $meta);
            $deposit = $to->deposit($amount * $rate, $meta);

            $transfers = app(CommonService::class)->multiBrings([
                (new Bring())
                    ->setStatus(Transfer::STATUS_EXCHANGE)
                    ->setDeposit($deposit)
                    ->setWithdraw($withdraw)
                    ->setFrom($from)
                    ->setTo($to)
            ]);

            return current($transfers);
        });
    }

}
