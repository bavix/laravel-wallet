<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\External\Dto\Extra;
use Bavix\Wallet\External\Dto\Option;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\UserMulti;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class ExtraTest extends TestCase
{
    public function testExtraTransferWithdraw(): void
    {
        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);
        self::assertSame(1000, $user1->balanceInt);

        $transfer = $user1->transfer(
            $user2,
            500,
            new Extra(
                deposit: [
                    'type' => 'extra-deposit',
                ],
                withdraw: new Option(
                    [
                        'type' => 'extra-withdraw',
                    ],
                    false
                ),
            )
        );

        self::assertSame(1000, $user1->balanceInt);
        self::assertSame(500, $user2->balanceInt);
        self::assertNotNull($transfer);

        self::assertSame([
            'type' => 'extra-deposit',
        ], $transfer->deposit->meta);
        self::assertSame([
            'type' => 'extra-withdraw',
        ], $transfer->withdraw->meta);
    }

    public function testExtraTransferDeposit(): void
    {
        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);
        self::assertSame(1000, $user1->balanceInt);

        $transfer = $user1->transfer(
            $user2,
            500,
            new Extra(
                deposit: new Option(
                    [
                        'type' => 'extra-deposit',
                    ],
                    false
                ),
                withdraw: [
                    'type' => 'extra-withdraw',
                ],
            )
        );

        self::assertSame(500, $user1->balanceInt);
        self::assertSame(0, $user2->balanceInt);
        self::assertNotNull($transfer);

        self::assertSame([
            'type' => 'extra-deposit',
        ], $transfer->deposit->meta);
        self::assertSame([
            'type' => 'extra-withdraw',
        ], $transfer->withdraw->meta);
    }

    public function testExtraExchangeDeposit(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'My RUB',
            'slug' => 'rub',
        ]);

        self::assertSame(0, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $rub->deposit(10_000);

        self::assertSame(10_000, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $transfer = $rub->exchange($usd, 10000, new Extra(
            deposit: [
                'message' => 'We credit to the dollar account',
            ],
            withdraw: new Option(
                [
                    'message' => 'Write off from the ruble account',
                ],
                false
            )
        ));

        self::assertSame(10_000, $rub->balanceInt);
        self::assertSame(147, $usd->balanceInt);
        self::assertSame(1.47, (float) $usd->balanceFloat); // $1.47
        self::assertSame(0, (int) $transfer->fee);
        self::assertSame(Transfer::STATUS_EXCHANGE, $transfer->status);
        self::assertSame([
            'message' => 'We credit to the dollar account',
        ], $transfer->deposit->meta);
        self::assertSame([
            'message' => 'Write off from the ruble account',
        ], $transfer->withdraw->meta);
    }

    public function testExtraExchangeWithdraw(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'My RUB',
            'slug' => 'rub',
        ]);

        self::assertSame(0, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $rub->deposit(10_000);

        self::assertSame(10_000, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $transfer = $rub->exchange($usd, 10000, new Extra(
            deposit: new Option(
                [
                    'message' => 'We credit to the dollar account',
                ],
                false
            ),
            withdraw: [
                'message' => 'Write off from the ruble account',
            ],
        ));

        self::assertSame(0, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);
        self::assertSame(0, (int) $transfer->fee);
        self::assertSame(Transfer::STATUS_EXCHANGE, $transfer->status);
        self::assertSame([
            'message' => 'We credit to the dollar account',
        ], $transfer->deposit->meta);
        self::assertSame([
            'message' => 'Write off from the ruble account',
        ], $transfer->withdraw->meta);
    }
}
