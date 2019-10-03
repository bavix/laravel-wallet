<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\UserCashier;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Database\QueryException;
use function compact;
use function range;

class MultiWalletTest extends TestCase
{

    /**
     * @return void
     */
    public function testCreateDefault(): void
    {
        $slug = config('wallet.wallet.default.slug', 'default');

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $this->assertNull($user->getWallet($slug));

        $wallet = $user->createWallet(['name' => 'Simple', 'slug' => $slug]);
        $this->assertNotNull($wallet);
        $this->assertNotNull($user->wallet);
        $this->assertEquals($user->wallet->id, $wallet->id);
    }

    /**
     * @return void
     */
    public function testDeposit(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $this->assertFalse($user->hasWallet('deposit'));
        $wallet = $user->createWallet([
            'name' => 'Deposit'
        ]);

        $this->assertTrue($user->hasWallet('deposit'));
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(10);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 10);

        $wallet->deposit(125);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 135);

        $wallet->deposit(865);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 1000);

        $this->assertEquals($user->transactions()->count(), 3);

        $wallet->withdraw($wallet->balance);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     */
    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $wallet->deposit(-1);
    }

    /**
     * @return void
     */
    public function testWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $this->assertEquals($wallet->balance, 100);

        $wallet->withdraw(10);
        $this->assertEquals($wallet->balance, 90);

        $wallet->withdraw(81);
        $this->assertEquals($wallet->balance, 9);

        $wallet->withdraw(9);
        $this->assertEquals($wallet->balance, 0);

        $wallet->withdraw(1);
    }

    /**
     * @return void
     */
    public function testInvalidWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $wallet->withdraw(-1);
    }

    /**
     * @return void
     */
    public function testTransfer(): void
    {
        /**
         * @var UserMulti $first
         * @var UserMulti $second
         */
        list($first, $second) = factory(UserMulti::class, 2)->create();
        $firstWallet = $first->createWallet([
            'name' => 'deposit'
        ]);

        $secondWallet = $second->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertNotEquals($first->id, $second->id);
        $this->assertNotEquals($firstWallet->id, $secondWallet->id);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);

        $firstWallet->deposit(100);
        $this->assertEquals($firstWallet->balance, 100);

        $secondWallet->deposit(100);
        $this->assertEquals($secondWallet->balance, 100);

        $transfer = $firstWallet->transfer($secondWallet, 100);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($second->balance, 0);
        $this->assertEquals($secondWallet->balance, 200);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        $this->assertEquals($secondWallet->balance, 100);
        $this->assertEquals($firstWallet->balance, 100);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        $this->assertEquals($secondWallet->balance, 0);
        $this->assertEquals($firstWallet->balance, 200);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $firstWallet->withdraw($firstWallet->balance);
        $this->assertEquals($firstWallet->balance, 0);

        $this->assertNull($firstWallet->safeTransfer($secondWallet, 100));
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);

        $transfer = $firstWallet->forceTransfer($secondWallet, 100);
        $this->assertNotNull($transfer);
        $this->assertEquals($firstWallet->balance, -100);
        $this->assertEquals($secondWallet->balance, 100);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->forceTransfer($firstWallet, 100);
        $this->assertNotNull($transfer);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);
    }

    /**
     * @return void
     */
    public function testTransferYourself(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $wallet->transfer($wallet, 100);
        $this->assertEquals($wallet->balance, 100);

        $wallet->withdraw($wallet->balance);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     */
    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);
        $wallet->withdraw(1);
    }

    /**
     * @return void
     */
    public function testConfirmed(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(1);
        $this->assertEquals($wallet->balance, 1);

        $wallet->withdraw(1, null, false);
        $this->assertEquals($wallet->balance, 1);

        $wallet->withdraw(1);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     */
    public function testWalletUnique(): void
    {
        $this->expectException(QueryException::class);

        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();

        $user->createWallet([
            'name' => 'deposit'
        ]);

        $user->createWallet([
            'name' => 'deposit'
        ]);
    }

    /**
     * @return void
     */
    public function testGetWallet(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();

        $firstWallet = $user->createWallet([
            'name' => 'My Test',
            'slug' => 'test',
        ]);

        $secondWallet = $user->getWallet('test');
        $this->assertEquals($secondWallet->getKey(), $firstWallet->getKey());

        $test2 = $user->wallets()->create([
            'name' => 'Test2'
        ]);

        $this->assertEquals(
            $test2->getKey(),
            $user->getWallet('test2')->getKey()
        );

        // check default wallet
        $this->assertEquals(
            $user->balance,
            $user->wallet->balance
        );
    }

    /**
     * @return void
     */
    public function testGetWalletOptimize(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $names = range('a', 'z');
        foreach ($names as $name) {
            $user->createWallet(compact('name'));
        }

        $user->load('wallets'); // optimize

        foreach ($names as $name) {
            $this->assertEquals($name, $user->getWallet($name)->name);
        }
    }

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var UserMulti $user
         * @var Item $product
         */
        $user = factory(UserMulti::class)->create();
        $a = $user->createWallet(['name' => 'a']);
        $b = $user->createWallet(['name' => 'b']);

        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($a->balance, 0);
        $this->assertEquals($b->balance, 0);

        $a->deposit($product->getAmountProduct($a));
        $this->assertEquals($a->balance, $product->getAmountProduct($a));

        $b->deposit($product->getAmountProduct($b));
        $this->assertEquals($b->balance, $product->getAmountProduct($b));

        $transfer = $a->pay($product);
        $paidTransfer = $a->paid($product);
        $this->assertTrue((bool)$paidTransfer);
        $this->assertEquals($transfer->getKey(), $paidTransfer->getKey());
        $this->assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        $this->assertEquals($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        $this->assertEquals($transfer->from->id, $a->id);
        $this->assertEquals($transfer->to->id, $product->id);
        $this->assertEquals($transfer->status, Transfer::STATUS_PAID);
        $this->assertEquals($a->balance, 0);
        $this->assertEquals($product->balance, $product->getAmountProduct($a));

        $transfer = $b->pay($product);
        $paidTransfer = $b->paid($product);
        $this->assertTrue((bool)$paidTransfer);
        $this->assertEquals($transfer->getKey(), $paidTransfer->getKey());
        $this->assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        $this->assertEquals($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        $this->assertEquals($transfer->from->id, $b->id);
        $this->assertEquals($transfer->to->id, $product->id);
        $this->assertEquals($transfer->status, Transfer::STATUS_PAID);
        $this->assertEquals($b->balance, 0);
        $this->assertEquals($product->balance, $product->getAmountProduct($b) * 2);

        $this->assertTrue($a->refund($product));
        $this->assertEquals($product->balance, $product->getAmountProduct($a));
        $this->assertEquals($a->balance, $product->getAmountProduct($a));

        $this->assertTrue($b->refund($product));
        $this->assertEquals($product->balance, 0);
        $this->assertEquals($b->balance, $product->getAmountProduct($b));
    }

    /**
     * @return void
     */
    public function testUserCashier(): void
    {
        /**
         * @var UserCashier $user
         */
        $user = factory(UserCashier::class)->create();
        $default = $user->wallet;

        $this->assertEquals($default->balance, 0);

        $transaction = $default->deposit(100);
        $this->assertEquals($transaction->type, Transaction::TYPE_DEPOSIT);
        $this->assertEquals($transaction->amount, 100);
        $this->assertEquals($default->balance, 100);

        $newWallet = $user->createWallet(['name' => 'New Wallet']);

        $transfer = $default->transfer($newWallet, 100);
        $this->assertEquals($default->balance, 0);
        $this->assertEquals($newWallet->balance, 100);

        $this->assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);
        $this->assertEquals($transfer->withdraw->amount, -100);

        $this->assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        $this->assertEquals($transfer->deposit->amount, 100);
    }

}
