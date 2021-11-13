<?php

namespace PHPSTORM_META {

    use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
    use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
    use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
    use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
    use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
    use Bavix\Wallet\Services\CastServiceInterface;
    use Bavix\Wallet\Internal\Service\JsonServiceInterface;
    use Bavix\Wallet\Internal\Service\PrepareServiceInterface;
    use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
    use Bavix\Wallet\Internal\Transform\TransferDtoTransformerInterface;
    use Bavix\Wallet\Models\Transaction;
    use Bavix\Wallet\Models\Transfer;
    use Bavix\Wallet\Models\Wallet;
    use Bavix\Wallet\Services\AssistantServiceInterface;
    use Bavix\Wallet\Services\AtmServiceInterface;
    use Bavix\Wallet\Services\BasketServiceInterface;
    use Bavix\Wallet\Services\BookkeeperServiceInterface;
    use Bavix\Wallet\Interfaces\CartInterface;
    use Bavix\Wallet\Services\ConsistencyServiceInterface;
    use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
    use Bavix\Wallet\Services\ExchangeServiceInterface;
    use Bavix\Wallet\Internal\Service\LockServiceInterface;
    use Bavix\Wallet\Internal\Service\MathServiceInterface;
    use Bavix\Wallet\Services\PurchaseServiceInterface;
    use Bavix\Wallet\Internal\Service\StorageServiceInterface;
    use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
    use Bavix\Wallet\Internal\Service\UuidServiceInterface;
    use Bavix\Wallet\Objects\Cart;
    use Bavix\Wallet\Services\CommonServiceLegacy;
    use Bavix\Wallet\Services\LockServiceLegacy;
    use Bavix\Wallet\Services\MetaServiceLegacy;
    use Bavix\Wallet\Services\WalletServiceLegacy;

    override(\app(0), map([
        // internal.assembler
        TransactionDtoAssemblerInterface::class => TransactionDtoAssemblerInterface::class,
        TransferDtoAssemblerInterface::class => TransferDtoAssemblerInterface::class,
        TransferLazyDtoAssemblerInterface::class => TransferLazyDtoAssemblerInterface::class,

        // internal.repositories
        TransactionRepositoryInterface::class => TransactionRepositoryInterface::class,
        TransferRepositoryInterface::class => TransferRepositoryInterface::class,

        // internal.service
        CastServiceInterface::class => CastServiceInterface::class,
        DatabaseServiceInterface::class => DatabaseServiceInterface::class,
        JsonServiceInterface::class => JsonServiceInterface::class,
        LockServiceInterface::class => LockServiceInterface::class,
        PrepareServiceInterface::class => PrepareServiceInterface::class,
        StorageServiceInterface::class => StorageServiceInterface::class,
        TranslatorServiceInterface::class => TranslatorServiceInterface::class,
        UuidServiceInterface::class => UuidServiceInterface::class,

        // internal.transform
        TransactionDtoTransformerInterface::class => TransactionDtoTransformerInterface::class,
        TransferDtoTransformerInterface::class => TransferDtoTransformerInterface::class,

        // legacy.models
        Wallet::class => Wallet::class,
        Transfer::class => Transfer::class,
        Transaction::class => Transaction::class,

        // legacy.objects
        Cart::class => Cart::class,

        // services
        AssistantServiceInterface::class => AssistantServiceInterface::class,
        AtmServiceInterface::class => AtmServiceInterface::class,
        BasketServiceInterface::class => BasketServiceInterface::class,
        BookkeeperServiceInterface::class => BookkeeperServiceInterface::class,
        ConsistencyServiceInterface::class => ConsistencyServiceInterface::class,
        ExchangeServiceInterface::class => ExchangeServiceInterface::class,
        MathServiceInterface::class => MathServiceInterface::class,
        PurchaseServiceInterface::class => PurchaseServiceInterface::class,

        // lagacy.services
        CommonServiceLegacy::class => CommonServiceLegacy::class,
        LockServiceLegacy::class => LockServiceLegacy::class,
        MetaServiceLegacy::class => MetaServiceLegacy::class,
        WalletServiceLegacy::class => WalletServiceLegacy::class,
    ]));

}
