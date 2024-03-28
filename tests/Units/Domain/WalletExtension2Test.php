<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Assembler\TransactionDtoAssemblerCustomUuid;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionOrderAndRef;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionOrderAndRefDtoTransformerDecorator;

/**
 * @internal
 */
final class WalletExtension2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // configuration's
        config([
            'wallet.transaction.model' => TransactionOrderAndRef::class,
        ]);
        config([
            'wallet.transformers.transaction' => TransactionOrderAndRef::class,
        ]);

        // rebind
        $this->app?->bind(
            TransactionDtoTransformerInterface::class,
            TransactionOrderAndRefDtoTransformerDecorator::class
        );
        $this->app?->bind(Transaction::class, TransactionOrderAndRef::class);
    }

    public function testCustomOrderAndRefAttribute(): void
    {
        $uuid = app(UuidFactoryServiceInterface::class);
        $orderId = $uuid->uuid4();
        $refId = $uuid->uuid4();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));

        /** @var TransactionOrderAndRef $transaction */
        $transaction = $buyer->deposit(1000, [
            'order_id' => $orderId,
            'ref_id' => $refId,
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(TransactionOrderAndRef::class, $transaction);
        self::assertSame($orderId, $transaction->order_id);
        self::assertSame($refId, $transaction->ref_id);
    }

    public function testCustomUuidForAssembler(): void
    {
        $this->app?->bind(
            TransactionDtoAssemblerInterface::class,
            TransactionDtoAssemblerCustomUuid::class,
        );

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));

        /** @var TransactionOrderAndRef $transaction */
        $transaction = $buyer->deposit(1000);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertSame("00000000-e26c-48af-8f61-284e37d3f18e", $transaction->uuid);
    }
}
