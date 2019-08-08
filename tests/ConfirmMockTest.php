<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Common\Models\Wallet;
use Bavix\Wallet\Test\Models\UserConfirm;

class ConfirmMockTest extends TestCase
{

    /**
     * @return void
     */
    public function testFailConfirm(): void
    {
        /**
         * @var UserConfirm $userConfirm
         */
        $userConfirm = factory(UserConfirm::class)->create();
        $transaction = $userConfirm->deposit(100, null, false);
        $this->assertEquals($userConfirm->wallet->id, $transaction->wallet->id);
        $this->assertEquals($userConfirm->id, $transaction->payable_id);
        $this->assertInstanceOf(UserConfirm::class, $transaction->payable);
        $this->assertFalse($transaction->confirmed);

        $wallet = app(WalletService::class)
            ->getWallet($userConfirm);

        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('refreshBalance')
            ->willReturn(false);

        /**
         * @var Wallet $mockWallet
         */
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertFalse($mockWallet->refreshBalance());

        $userConfirm->setRelation('wallet', $mockWallet);
        $this->assertFalse($userConfirm->confirm($transaction));
    }

}
