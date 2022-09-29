<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Api;

use function app;
use Bavix\Wallet\External\Api\TransactionQuery;
use Bavix\Wallet\External\Api\TransactionQueryHandlerInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class TransactionHandlerTest extends TestCase
{
    public function testWalletNotExists(): void
    {
        /** @var TransactionQueryHandlerInterface $transactionHandler */
        $transactionHandler = app(TransactionQueryHandlerInterface::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertFalse($buyer->wallet->exists);

        $transactions = $transactionHandler->apply([
            TransactionQuery::createDeposit($buyer, 101, null),
            TransactionQuery::createDeposit($buyer, 100, null),
            TransactionQuery::createDeposit($buyer, 100, null),
            TransactionQuery::createDeposit($buyer, 100, null),
            TransactionQuery::createWithdraw($buyer, 400, null),
        ]);

        self::assertSame(1, $buyer->balanceInt);
        self::assertCount(5, $transactions);

        self::assertCount(
            4,
            array_filter($transactions, static fn ($t) => $t->type === Transaction::TYPE_DEPOSIT),
        );
        self::assertCount(
            1,
            array_filter($transactions, static fn ($t) => $t->type === Transaction::TYPE_WITHDRAW),
        );
    }
}
