<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\UserConfirmFactory;
use Bavix\Wallet\Test\Infra\Factories\UserFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\User;
use Bavix\Wallet\Test\Infra\Models\UserConfirm;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class ConfirmTest extends TestCase
{
    public function testSimple(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->deposit(1000, [
            'desc' => 'unconfirmed',
        ], false);
        self::assertTrue($transaction->getKey() > 0);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);

        $wallet->confirm($transaction);
        self::assertSame($transaction->amountInt, (int) app(BookkeeperServiceInterface::class)->amount($wallet));
        self::assertSame($transaction->amountInt, (int) app(RegulatorServiceInterface::class)->amount($wallet));
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($wallet));
        self::assertSame($transaction->amountInt, $wallet->balanceInt);
        self::assertTrue($transaction->confirmed);
    }

    public function testSafe(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->forceWithdraw(1000, [
            'desc' => 'unconfirmed',
        ], false);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);
        self::assertTrue($transaction->getKey() > 0);

        $wallet->safeConfirm($transaction);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);
    }

    public function testSafeResetConfirm(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->forceWithdraw(1000, [
            'desc' => 'confirmed',
        ]);
        self::assertSame(-1000, $wallet->balanceInt);
        self::assertTrue($transaction->confirmed);

        $wallet->safeResetConfirm($transaction);
        self::assertSame(0, (int) app(BookkeeperServiceInterface::class)->amount($wallet));
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->amount($wallet));
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($wallet));
        self::assertSame(0, $wallet->balanceInt);
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

        self::assertSame(100, $wallet->balanceInt);

        $transaction = $wallet->withdraw(50, [
            'desc' => 'unconfirmed',
        ], false);
        self::assertSame(100, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);
    }

    public function testForce(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->forceWithdraw(1000, [
            'desc' => 'unconfirmed',
        ], false);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);

        $wallet->forceConfirm($transaction);
        self::assertSame($transaction->amountInt, $wallet->balanceInt);
        self::assertTrue($transaction->confirmed);
    }

    public function testUnconfirmed(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->forceWithdraw(1000, [
            'desc' => 'confirmed',
        ]);
        self::assertSame(-1000, $wallet->balanceInt);
        self::assertTrue($transaction->confirmed);

        $wallet->resetConfirm($transaction);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);
    }

    public function testConfirmedInvalid(): void
    {
        $this->expectException(ConfirmedInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::CONFIRMED_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.confirmed_invalid'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->deposit(1000);
        self::assertSame(1000, $wallet->balanceInt);
        self::assertTrue($transaction->confirmed);

        $wallet->confirm($transaction);
    }

    public function testUnconfirmedInvalid(): void
    {
        $this->expectException(UnconfirmedInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::UNCONFIRMED_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.unconfirmed_invalid'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->deposit(1000, null, false);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);

        $wallet->resetConfirm($transaction);
    }

    public function testSafeUnconfirmed(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $transaction = $wallet->deposit(1000, null, false);
        self::assertSame(0, $wallet->balanceInt);
        self::assertFalse($transaction->confirmed);
        self::assertFalse($wallet->safeResetConfirm($transaction));
    }

    public function testWalletOwnerInvalid(): void
    {
        $this->expectException(WalletOwnerInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::WALLET_OWNER_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.owner_invalid'));

        /**
         * @var Buyer $first
         * @var Buyer $second
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $firstWallet = $first->wallet;
        $secondWallet = $second->wallet;

        self::assertSame(0, $firstWallet->balanceInt);

        $transaction = $firstWallet->deposit(1000, [
            'desc' => 'unconfirmed',
        ], false);
        self::assertSame(0, $firstWallet->balanceInt);
        self::assertFalse($transaction->confirmed);

        $secondWallet->confirm($transaction);
    }

    public function testUserConfirm(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->deposit(100, null, false);
        self::assertSame($transaction->wallet->id, $userConfirm->wallet->id);
        self::assertSame((int) $transaction->payable_id, (int) $userConfirm->id);
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
        self::assertSame(10000, $userConfirm->balanceInt);

        self::assertTrue($transaction->wallet->confirm($transaction));
        self::assertSame(9000, $userConfirm->balanceInt);
    }

    public function testUserConfirmByWallet(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->wallet->deposit(100, null, false);
        self::assertSame($transaction->wallet->id, $userConfirm->wallet->id);
        self::assertSame((int) $transaction->payable_id, (int) $userConfirm->id);
        self::assertInstanceOf(UserConfirm::class, $transaction->payable);
        self::assertFalse($transaction->confirmed);

        self::assertTrue($userConfirm->confirm($transaction));
        self::assertTrue($transaction->confirmed);
        self::assertTrue($userConfirm->resetConfirm($transaction));
        self::assertFalse($transaction->confirmed);
        self::assertTrue($userConfirm->wallet->confirm($transaction));
        self::assertTrue($transaction->confirmed);
    }

    public function testTransactionResetConfirmWalletOwnerInvalid(): void
    {
        $this->expectException(WalletOwnerInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::WALLET_OWNER_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.owner_invalid'));

        /**
         * @var User $user1
         * @var User $user2
         */
        [$user1, $user2] = UserFactory::times(2)->create();
        $user1->deposit(1000);

        self::assertSame(1000, $user1->balanceInt);

        $transfer = $user1->transfer($user2, 500);
        $user1->wallet->resetConfirm($transfer->deposit);
    }

    public function testTransactionResetConfirmSuccess(): void
    {
        /**
         * @var User $user1
         * @var User $user2
         */
        [$user1, $user2] = UserFactory::times(2)->create();
        $user1->deposit(1000);

        self::assertSame(1000, $user1->balanceInt);
        app(DatabaseServiceInterface::class)->transaction(static function () use ($user1, $user2) {
            $transfer = $user1->transfer($user2, 500);
            self::assertTrue($user2->wallet->resetConfirm($transfer->deposit)); // confirm => false
        });

        self::assertSame(500, (int) $user1->transactions()->sum('amount'));
        self::assertSame(500, (int) $user2->transactions()->sum('amount'));

        self::assertSame(500, $user1->balanceInt);
        self::assertSame(0, $user2->balanceInt);
    }
}
