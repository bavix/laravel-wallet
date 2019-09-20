<?php

namespace PHPSTORM_META {

    use Bavix\Wallet\Interfaces\Rateable;
    use Bavix\Wallet\Interfaces\Storable;
    use Bavix\Wallet\Models\Transaction;
    use Bavix\Wallet\Models\Transfer;
    use Bavix\Wallet\Models\Wallet;
    use Bavix\Wallet\Objects\Bring;
    use Bavix\Wallet\Objects\Cart;
    use Bavix\Wallet\Objects\EmptyLock;
    use Bavix\Wallet\Objects\Operation;
    use Bavix\Wallet\Services\CommonService;
    use Bavix\Wallet\Services\ExchangeService;
    use Bavix\Wallet\Services\ProxyService;
    use Bavix\Wallet\Services\WalletService;

    override(\app(0), map([
        Cart::class => Cart::class,
        Bring::class => Bring::class,
        Operation::class => Operation::class,
        EmptyLock::class => EmptyLock::class,
        ExchangeService::class => ExchangeService::class,
        CommonService::class => CommonService::class,
        ProxyService::class => ProxyService::class,
        WalletService::class => WalletService::class,
        Wallet::class => Wallet::class,
        Transfer::class => Transfer::class,
        Transaction::class => Transaction::class,
        Rateable::class => Rateable::class,
        Storable::class => Storable::class,
    ]));

}
