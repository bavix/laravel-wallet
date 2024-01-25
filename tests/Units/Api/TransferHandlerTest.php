<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Api;

use Bavix\Wallet\External\Api\TransferFloatQuery;
use Bavix\Wallet\External\Api\TransferQuery;
use Bavix\Wallet\External\Api\TransferQueryHandlerInterface;
use Bavix\Wallet\External\Dto\Extra;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use function app;

/**
 * @internal
 */
final class TransferHandlerTest extends TestCase
{
    public function testWalletNotExists(): void
    {
        /** @var TransferQueryHandlerInterface $transferQueryHandler */
        $transferQueryHandler = app(TransferQueryHandlerInterface::class);

        /** @var Buyer $from */
        /** @var Buyer $to */
        [$from, $to] = BuyerFactory::times(2)->create();

        self::assertFalse($from->relationLoaded('wallet'));
        self::assertFalse($from->wallet->exists);
        self::assertFalse($to->relationLoaded('wallet'));
        self::assertFalse($to->wallet->exists);

        $transfers = $transferQueryHandler->apply([
            new TransferQuery(
                $from,
                $to,
                100,
                new Extra(
                    null,
                    null,
                    '598c184c-e6d6-4fc2-9640-c1c7acb38093',
                    ['type' => 'first'],
                ),
            ),
            new TransferQuery($from, $to, 100,
                new Extra(
                    null,
                    null,
                    'f303d60d-c2de-45d0-b9ed-e1487429709a',
                    ['type' => 'second'],
                )),
            new TransferQuery($to, $from, 50,
                new Extra(
                    null,
                    null,
                    '7f0175fe-99cc-4058-92c6-157f0da18243',
                    ['type' => 'third'],
                )),
            new TransferFloatQuery($to, $from, .50,
                new Extra(
                    null,
                    null,
                    '1a7326a6-dfdf-4ec8-afc4-cb21cf1f43c6',
                    ['type' => 'fourth'],
                )),
        ]);

        self::assertSame(-100, $from->balanceInt);
        self::assertSame(100, $to->balanceInt);
        self::assertCount(4, $transfers);

        self::assertSame(['type' => 'first'], $transfers['598c184c-e6d6-4fc2-9640-c1c7acb38093']->extra);
        self::assertSame(['type' => 'second'], $transfers['f303d60d-c2de-45d0-b9ed-e1487429709a']->extra);
        self::assertSame(['type' => 'third'], $transfers['7f0175fe-99cc-4058-92c6-157f0da18243']->extra);
        self::assertSame(['type' => 'fourth'], $transfers['1a7326a6-dfdf-4ec8-afc4-cb21cf1f43c6']->extra);
    }
}
