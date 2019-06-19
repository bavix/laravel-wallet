<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\CurrencyService;
use Illuminate\Support\Facades\DB;

trait CanExchange
{

    /**
     * @inheritDoc
     */
    public function exchange(Wallet $to, int $amount): Transfer
    {
        // todo: check
        return $this->forceExchange($to, $amount);
    }

    /**
     * @inheritDoc
     */
    public function safeExchange(Wallet $to, int $amount): ?Transfer
    {
       try {
           return $this->exchange($to, $amount);
       } catch (\Throwable $throwable) {
           return null;
       }
    }

    /**
     * @inheritDoc
     */
    public function forceExchange(Wallet $to, int $amount): Transfer
    {
        /**
         * @var Wallet $from
         */
        $from = $this;

        return DB::transaction(function () use ($from, $to, $amount) {
            $rate = app(CurrencyService::class)->rate($from, $to);
            $withdraw = $this->withdraw($amount);
            $deposit = $to->deposit($amount * $rate);

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
