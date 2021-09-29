<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\UserConfirmFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\UserConfirm;

/**
 * @internal
 */
class ConfirmTest extends TestCase
{
    public function testSimple(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);

        $wallet->confirm($transaction);
        self::assertEquals($transaction->amount, $wallet->balance);
        self::assertTrue($transaction->confirmed);
    }

    public function testSafe(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);

        $wallet->safeConfirm($transaction);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);
    }

    public function testSafeResetConfirm(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'confirmed']);
        self::assertEquals(-1000, $wallet->balance);
        self::assertTrue($transaction->confirmed);

        $wallet->safeResetConfirm($transaction);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/134
     */
    public function testWithdraw(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $wallet->deposit(100);

        self::assertEquals(100, $wallet->balance);

        $transaction = $wallet->withdraw(50, ['desc' => 'unconfirmed'], false);
        self::assertEquals(100, $wallet->balance);
        self::assertFalse($transaction->confirmed);
    }

    public function testForce(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);

        $wallet->forceConfirm($transaction);
        self::assertEquals($transaction->amount, $wallet->balance);
        self::assertTrue($transaction->confirmed);
    }

    public function testUnconfirmed(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'confirmed']);
        self::assertEquals(-1000, $wallet->balance);
        self::assertTrue($transaction->confirmed);

        $wallet->resetConfirm($transaction);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);
    }

    public function testConfirmedInvalid(): void
    {
        $this->expectException(ConfirmedInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.confirmed_invalid'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000);
        self::assertEquals(1000, $wallet->balance);
        self::assertTrue($transaction->confirmed);

        $wallet->confirm($transaction);
    }

    public function testUnconfirmedInvalid(): void
    {
        $this->expectException(UnconfirmedInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.unconfirmed_invalid'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000, null, false);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);

        $wallet->resetConfirm($transaction);
    }

    public function testSafeUnconfirmed(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000, null, false);
        self::assertEquals(0, $wallet->balance);
        self::assertFalse($transaction->confirmed);
        self::assertFalse($wallet->safeResetConfirm($transaction));
    }

    public function testWalletOwnerInvalid(): void
    {
        $this->expectException(WalletOwnerInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.owner_invalid'));

        /**
         * @var Buyer $first
         * @var Buyer $second
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $firstWallet = $first->wallet;
        $secondWallet = $second->wallet;

        self::assertEquals(0, $firstWallet->balance);

        $transaction = $firstWallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        self::assertEquals(0, $firstWallet->balance);
        self::assertFalse($transaction->confirmed);

        $secondWallet->confirm($transaction);
    }

    public function testUserConfirm(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->deposit(100, null, false);
        self::assertEquals($transaction->wallet->id, $userConfirm->wallet->id);
        self::assertEquals($transaction->payable_id, $userConfirm->id);
        self::assertInstanceOf(UserConfirm::class, $transaction->payable);
        self::assertFalse($transaction->confirmed);

        self::assertTrue($userConfirm->confirm($transaction));
        self::assertTrue($transaction->confirmed);
    }

    public function testConfirmWithoutWallet(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $userConfirm->deposit(10000);

        $transaction = $userConfirm->withdraw(1000, null, false);
        self::assertFalse($transaction->confirmed);
        self::assertEquals(10000, $userConfirm->balance);

        self::assertTrue($transaction->wallet->confirm($transaction));
        self::assertEquals(9000, $userConfirm->balance);
    }

    public function testUserConfirmByWallet(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->wallet->deposit(100, null, false);
        self::assertEquals($transaction->wallet->id, $userConfirm->wallet->id);
        self::assertEquals($transaction->payable_id, $userConfirm->id);
        self::assertInstanceOf(UserConfirm::class, $transaction->payable);
        self::assertFalse($transaction->confirmed);

        self::assertTrue($userConfirm->confirm($transaction));
        self::assertTrue($transaction->confirmed);
        self::assertTrue($userConfirm->resetConfirm($transaction));
        self::assertFalse($transaction->confirmed);
        self::assertTrue($userConfirm->wallet->confirm($transaction));
        self::assertTrue($transaction->confirmed);
    }
}
