<?php

namespace PHPSTORM_META {

    use Bavix\Wallet\Internal\BasketInterface;
    use Bavix\Wallet\Internal\BookkeeperInterface;
    use Bavix\Wallet\Internal\CartInterface;
    use Bavix\Wallet\Internal\ConsistencyInterface;
    use Bavix\Wallet\Internal\ExchangeInterface;
    use Bavix\Wallet\Internal\LockInterface;
    use Bavix\Wallet\Internal\MathInterface;
    use Bavix\Wallet\Internal\PurchaseInterface;
    use Bavix\Wallet\Internal\StorageInterface;
    use Bavix\Wallet\Internal\UuidInterface;
    use Bavix\Wallet\Objects\Cart;
    use Bavix\Wallet\Services\CommonService;
    use Bavix\Wallet\Services\DbService;
    use Bavix\Wallet\Services\LockService;
    use Bavix\Wallet\Services\MetaService;
    use Bavix\Wallet\Services\WalletService;

    override(\app(0), map([
        BasketInterface::class => BasketInterface::class,
        BookkeeperInterface::class => BookkeeperInterface::class,
        CartInterface::class => CartInterface::class,
        ConsistencyInterface::class => ConsistencyInterface::class,
        ExchangeInterface::class => ExchangeInterface::class,
        LockInterface::class => LockInterface::class,
        MathInterface::class => MathInterface::class,
        PurchaseInterface::class => PurchaseInterface::class,
        StorageInterface::class => StorageInterface::class,
        UuidInterface::class => UuidInterface::class,

        // deprecated's
        Cart::class => Cart::class,

        // old
        CommonService::class => CommonService::class,
        DbService::class => DbService::class,
        LockService::class => LockService::class,
        MetaService::class => MetaService::class,
        WalletService::class => WalletService::class,
    ]));

}
