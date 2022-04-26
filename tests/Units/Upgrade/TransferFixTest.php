<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Upgrade;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class TransferFixTest extends TestCase
{
    public function testTransferFixCommand(): void
    {
        /** @var Buyer $buyer1 */
        /** @var Buyer $buyer2 */
        [$buyer1, $buyer2] = BuyerFactory::times(2)->create();
        $transfer = $buyer1->forceTransfer($buyer2, 1_000_000);

        self::assertSame($buyer1->wallet->getMorphClass(), $transfer->from->getMorphClass());
        self::assertSame($buyer1->wallet->getKey(), $transfer->from->getKey());

        self::assertSame($buyer2->wallet->getMorphClass(), $transfer->to->getMorphClass());
        self::assertSame($buyer2->wallet->getKey(), $transfer->to->getKey());

        $buyer1->wallet->transfers()
            ->update([
                'from_type' => $buyer1->getMorphClass(),
                'from_id' => $buyer1->getKey(),

                'to_type' => $buyer2->getMorphClass(),
                'to_id' => $buyer2->getKey(),
            ])
        ;

        $transfer->refresh();
        $transfer->from->refresh();
        $transfer->to->refresh();

        self::assertSame($buyer1->getMorphClass(), $transfer->from->getMorphClass());
        self::assertSame($buyer1->getKey(), $transfer->from->getKey());

        self::assertSame($buyer2->getMorphClass(), $transfer->to->getMorphClass());
        self::assertSame($buyer2->getKey(), $transfer->to->getKey());

        $this->artisan('bx:transfer:fix')
            ->assertSuccessful()
        ;

        $transfer->refresh();
        $transfer->from->refresh();
        $transfer->to->refresh();

        self::assertSame($buyer1->wallet->getMorphClass(), $transfer->from->getMorphClass());
        self::assertSame($buyer1->wallet->getKey(), $transfer->from->getKey());

        self::assertSame($buyer2->wallet->getMorphClass(), $transfer->to->getMorphClass());
        self::assertSame($buyer2->wallet->getKey(), $transfer->to->getKey());
    }
}
