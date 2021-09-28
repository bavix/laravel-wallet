<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Common\Models\Wallet;
use Bavix\Wallet\Test\Factories\UserConfirmFactory;
use Bavix\Wallet\Test\Models\UserConfirm;

/**
 * @internal
 */
class ConfirmMockTest extends TestCase
{
    public function testFailConfirm(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->deposit(100, null, false);
        self::assertEquals($userConfirm->wallet->id, $transaction->wallet->id);
        self::assertEquals($userConfirm->id, $transaction->payable_id);
        self::assertInstanceOf(UserConfirm::class, $transaction->payable);
        self::assertFalse($transaction->confirmed);

        $wallet = app(WalletService::class)
            ->getWallet($userConfirm)
        ;

        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('refreshBalance')
            ->willReturn(false)
        ;

        /**
         * @var Wallet $mockWallet
         */
        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertFalse($mockWallet->refreshBalance());

        $userConfirm->setRelation('wallet', $mockWallet);
        self::assertFalse($userConfirm->confirm($transaction));
        self::assertFalse($userConfirm->safeConfirm($transaction));
    }

    public function testFailResetConfirm(): void
    {
        /** @var UserConfirm $userConfirm */
        $userConfirm = UserConfirmFactory::new()->create();
        $transaction = $userConfirm->deposit(100);
        self::assertEquals($userConfirm->wallet->id, $transaction->wallet->id);
        self::assertEquals($userConfirm->id, $transaction->payable_id);
        self::assertInstanceOf(UserConfirm::class, $transaction->payable);
        self::assertTrue($transaction->confirmed);

        $wallet = app(WalletService::class)
            ->getWallet($userConfirm)
        ;

        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('refreshBalance')
            ->willReturn(false)
        ;

        /**
         * @var Wallet $mockWallet
         */
        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertFalse($mockWallet->refreshBalance());

        $userConfirm->setRelation('wallet', $mockWallet);
        self::assertFalse($userConfirm->resetConfirm($transaction));
        self::assertFalse($userConfirm->safeResetConfirm($transaction));
    }
}
