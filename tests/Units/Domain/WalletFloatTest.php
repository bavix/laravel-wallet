<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Factories\UserFloatFactory;
use Bavix\Wallet\Test\Infra\Models\UserFloat as User;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class WalletFloatTest extends TestCase
{
    public function testDeposit(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);
        self::assertSame(0., (float) $user->balanceFloat);

        $user->depositFloat(.1);
        self::assertSame(10, $user->balanceInt);
        self::assertSame(.1, (float) $user->balanceFloat);

        $user->depositFloat(1.25);
        self::assertSame(135, $user->balanceInt);
        self::assertSame(1.35, (float) $user->balanceFloat);

        $user->deposit(865);
        self::assertSame(1000, $user->balanceInt);
        self::assertSame(10., (float) $user->balanceFloat);

        self::assertSame(3, $user->transactions()->count());

        $user->withdraw($user->balance);
        self::assertSame(0, $user->balanceInt);
        self::assertSame(0., (float) $user->balanceFloat);
    }

    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::AMOUNT_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));
        $user = UserFloatFactory::new()->create();
        $user->depositFloat(-1);
    }

    public function testWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->depositFloat(1);
        self::assertSame(1., (float) $user->balanceFloat);

        $user->withdrawFloat(.1);
        self::assertSame(0.9, (float) $user->balanceFloat);

        $user->withdrawFloat(.81);
        self::assertSame(.09, (float) $user->balanceFloat);

        $user->withdraw(9);
        self::assertSame(0, $user->balanceInt);

        $user->withdraw(1);
    }

    public function testInvalidWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));
        $user = UserFloatFactory::new()->create();
        $user->withdrawFloat(-1);
    }

    public function testTransfer(): void
    {
        /**
         * @var User $first
         * @var User $second
         */
        [$first, $second] = UserFloatFactory::times(2)->create();
        self::assertNotSame($first->getKey(), $second->getKey());
        self::assertSame((float) $first->balanceFloat, 0.);
        self::assertSame((float) $second->balanceFloat, 0.);

        $first->depositFloat(1);
        self::assertSame((float) $first->balanceFloat, 1.);

        $second->depositFloat(1);
        self::assertSame((float) $second->balanceFloat, 1.);

        $first->transferFloat($second, 1);
        self::assertSame((float) $first->balanceFloat, 0.);
        self::assertSame((float) $second->balanceFloat, 2.);

        $second->transferFloat($first, 1);
        self::assertSame((float) $second->balanceFloat, 1.);
        self::assertSame((float) $first->balanceFloat, 1.);

        $second->transferFloat($first, 1);
        self::assertSame((float) $second->balanceFloat, 0.);
        self::assertSame((float) $first->balanceFloat, 2.);

        $first->withdrawFloat($first->balanceFloat);
        self::assertSame((float) $first->balanceFloat, 0.);

        self::assertNull($first->safeTransferFloat($second, 1));
        self::assertSame((float) $first->balanceFloat, 0.);
        self::assertSame((float) $second->balanceFloat, 0.);

        self::assertNotNull($first->forceTransferFloat($second, 1));
        self::assertSame((float) $first->balanceFloat, -1.);
        self::assertSame((float) $second->balanceFloat, 1.);

        self::assertNotNull($second->forceTransferFloat($first, 1));
        self::assertSame((float) $first->balanceFloat, 0.);
        self::assertSame((float) $second->balanceFloat, 0.);
    }

    public function testTransferYourself(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0., (float) $user->balanceFloat);

        $user->depositFloat(1);
        $user->transferFloat($user, 1);
        self::assertSame(100, $user->balanceInt);

        $user->withdrawFloat($user->balanceFloat);
        self::assertSame(0, $user->balanceInt);
    }

    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);
        $user->withdrawFloat(1);
    }

    public function testConfirmed(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame($user->balanceInt, 0);

        $user->depositFloat(1);
        self::assertSame((float) $user->balanceFloat, 1.);

        $user->withdrawFloat(1, null, false);
        self::assertSame((float) $user->balanceFloat, 1.);

        self::assertTrue($user->canWithdrawFloat(1));
        $user->withdrawFloat(1);
        self::assertFalse($user->canWithdrawFloat(1));
        $user->forceWithdrawFloat(1);
        self::assertSame((float) $user->balanceFloat, -1.);
        $user->depositFloat(1);
        self::assertSame((float) $user->balanceFloat, 0.);
    }

    public function testMantissa(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(1_000_000);
        self::assertSame($user->balanceInt, 1_000_000);
        self::assertSame((float) $user->balanceFloat, 10000.00);

        $transaction = $user->withdrawFloat(2556.72);
        self::assertSame($transaction->amountInt, -255672);
        self::assertSame((float) $transaction->amountFloat, -2556.72);
        self::assertSame($transaction->type, Transaction::TYPE_WITHDRAW);

        self::assertSame($user->balanceInt, 1_000_000 - 255672);
        self::assertSame((float) $user->balanceFloat, 7443.28);

        $transaction = $user->depositFloat(2556.72 * 2);
        self::assertSame($transaction->amountInt, 255672 * 2);
        self::assertSame((float) $transaction->amountFloat, 2556.72 * 2);
        self::assertSame($transaction->type, Transaction::TYPE_DEPOSIT);

        self::assertSame($user->balanceInt, 1_000_000 + 255672);
        self::assertSame((float) $user->balanceFloat, 10000.00 + 2556.72);
    }

    public function testUpdateTransaction(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(1_000_000);
        self::assertSame(1_000_000, $user->balanceInt);
        self::assertSame(10000.00, (float) $user->balanceFloat);

        $transaction = $user->withdrawFloat(2556.72);
        self::assertSame(-255672, $transaction->amountInt);
        self::assertSame(-2556.72, (float) $transaction->amountFloat);
        self::assertSame(Transaction::TYPE_WITHDRAW, $transaction->type);

        $transaction->type = Transaction::TYPE_DEPOSIT;
        $transaction->amountFloat = 2556.72;
        self::assertTrue($transaction->save());
        self::assertTrue($user->wallet->refreshBalance());

        self::assertSame(255672, $transaction->amountInt);
        self::assertSame(2556.72, (float) $transaction->amountFloat);
        self::assertSame(Transaction::TYPE_DEPOSIT, $transaction->type);

        self::assertSame($user->balanceInt, 1_000_000 + 255672);
        self::assertSame((float) $user->balanceFloat, 10000.00 + 2556.72);
    }

    public function testMathRounding(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(1_000_000);
        self::assertSame(1_000_000, $user->balanceInt);
        self::assertSame(10000.00, (float) $user->balanceFloat);

        $transaction = $user->withdrawFloat(0.2 + 0.1);
        self::assertSame($transaction->amountInt, -30);
        self::assertSame($transaction->type, Transaction::TYPE_WITHDRAW);

        $transaction = $user->withdrawFloat(0.2 + 0.105);
        self::assertSame($transaction->amountInt, -31);
        self::assertSame($transaction->type, Transaction::TYPE_WITHDRAW);

        $transaction = $user->withdrawFloat(0.2 + 0.104);
        self::assertSame($transaction->amountInt, -30);
        self::assertSame($transaction->type, Transaction::TYPE_WITHDRAW);
    }

    public function testEther(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->wallet->decimal_places = 18;
        $user->wallet->save();

        $math = app(MathServiceInterface::class);

        $user->depositFloat('545.8754855274419');
        self::assertSame('545875485527441900000', $user->balance);
        self::assertSame(0, $math->compare($user->balanceFloat, '545.8754855274419'));
    }

    public function testBitcoin(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->wallet->decimal_places = 32; // bitcoin wallet
        $user->wallet->save();

        $math = app(MathServiceInterface::class);

        // optimize
        app(DatabaseServiceInterface::class)->transaction(function () use ($user) {
            for ($i = 0; $i < 256; ++$i) {
                $user->depositFloat('0.00000001'); // Satoshi
            }
        });

        self::assertSame($user->balance, '256'.str_repeat('0', 32 - 8));
        self::assertSame(0, $math->compare($user->balanceFloat, '0.00000256'));

        $user->deposit(256 .str_repeat('0', 32));
        $user->depositFloat('0.'.str_repeat('0', 31).'1');

        [$q, $r] = explode('.', $user->balanceFloat, 2);
        self::assertSame(strlen($r), $user->wallet->decimal_places);
        self::assertSame('25600000256000000000000000000000001', $user->balance);
        self::assertSame('256.00000256000000000000000000000001', $user->balanceFloat);
    }

    /**
     * Case from @ucanbehack.
     *
     * @see https://github.com/bavix/laravel-wallet/issues/149
     */
    public function testBitcoin2(): void
    {
        /** @var User $user */
        $user = UserFloatFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->wallet->decimal_places = 8;
        $user->wallet->save();

        $user->depositFloat(0.09699977);

        $user->wallet->refreshBalance();
        $user->refresh();

        self::assertSame(0.09699977, (float) $user->balanceFloat);
        self::assertSame(9_699_977, $user->balanceInt);
    }
}
