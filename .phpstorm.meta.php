<?php

namespace PHPSTORM_META {

    use Bavix\Wallet\Models\Transaction;
    use Bavix\Wallet\Models\Transfer;
    use Bavix\Wallet\Models\Wallet;
    use Bavix\Wallet\Services\CommonService;
    use Bavix\Wallet\Services\ExchangeService;
    use Bavix\Wallet\Services\ProxyService;
    use Bavix\Wallet\Services\WalletService;

    override(\app(0), map([
        ExchangeService::class => ExchangeService::class,
        CommonService::class => CommonService::class,
        ProxyService::class => ProxyService::class,
        WalletService::class => WalletService::class,
        Wallet::class => Wallet::class,
        Transfer::class => Transfer::class,
        Transaction::class => Transaction::class,
    ]));

}
