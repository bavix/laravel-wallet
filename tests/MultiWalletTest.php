<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\UserCashier;
use Bavix\Wallet\Test\Models\UserMulti;
use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\PostgresConnection;
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
        self::assertNull($user->getWallet($slug));

        $wallet = $user->createWallet(['name' => 'Simple', 'slug' => $slug]);
        self::assertNotNull($wallet);
        self::assertNotNull($user->wallet);
        self::assertEquals($user->wallet->id, $wallet->id);
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
        self::assertFalse($user->hasWallet('deposit'));
        $wallet = $user->createWallet([
            'name' => 'Deposit'
        ]);

        self::assertTrue($user->hasWallet('deposit'));
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(10);
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 10);

        $wallet->deposit(125);
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 135);

        $wallet->deposit(865);
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 1000);

        self::assertEquals($user->transactions()->count(), 3);

        $wallet->withdraw($wallet->balance);
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 0);

        $transaction = $wallet->depositFloat(10.10);
        self::assertEquals($user->balance, 0);
        self::assertEquals($wallet->balance, 1010);
        self::assertEquals($wallet->balanceFloat, 10.10);

        $user->refresh();

        // is equal
        self::assertTrue($transaction->wallet->is($user->getWallet('deposit')));
        self::assertTrue($user->getWallet('deposit')->is($wallet));
        self::assertTrue($wallet->is($user->getWallet('deposit')));

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertEquals($wallet->balanceFloat, 0);
    }

    /**
     * @return void
     */
    public function testDepositFloat(): void
    {
        /**
         * @var UserMulti $userInit
         * @var UserMulti $userFind
         */
        $userInit = factory(UserMulti::class)->create();
        $wallet = $userInit->createWallet([
            'name' => 'my-simple-wallet',
            'slug' => $userInit->getKey()
        ]);

        // without find
        $wallet->depositFloat(100.1);

        self::assertEquals($wallet->balanceFloat, 100.1);
        self::assertEquals($wallet->balance, 10010);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertEquals($wallet->balanceFloat, 0);

        // find
        $userFind = UserMulti::query()->find($userInit->id); // refresh
        self::assertTrue($userInit->is($userFind));
        self::assertTrue($userFind->hasWallet($userInit->getKey()));

        $wallet = $userFind->getWallet($userInit->getKey());
        $wallet->depositFloat(100.1);

        self::assertEquals($wallet->balanceFloat, 100.1);
        self::assertEquals($wallet->balance, 10010);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertEquals($wallet->balanceFloat, 0);
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

        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        self::assertEquals($wallet->balance, 100);

        $wallet->withdraw(10);
        self::assertEquals($wallet->balance, 90);

        $wallet->withdraw(81);
        self::assertEquals($wallet->balance, 9);

        $wallet->withdraw(9);
        self::assertEquals($wallet->balance, 0);

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

        self::assertNotEquals($first->id, $second->id);
        self::assertNotEquals($firstWallet->id, $secondWallet->id);
        self::assertEquals($firstWallet->balance, 0);
        self::assertEquals($secondWallet->balance, 0);

        $firstWallet->deposit(100);
        self::assertEquals($firstWallet->balance, 100);

        $secondWallet->deposit(100);
        self::assertEquals($secondWallet->balance, 100);

        $transfer = $firstWallet->transfer($secondWallet, 100);
        self::assertEquals($first->balance, 0);
        self::assertEquals($firstWallet->balance, 0);
        self::assertEquals($second->balance, 0);
        self::assertEquals($secondWallet->balance, 200);
        self::assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertEquals($secondWallet->balance, 100);
        self::assertEquals($firstWallet->balance, 100);
        self::assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertEquals($secondWallet->balance, 0);
        self::assertEquals($firstWallet->balance, 200);
        self::assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $firstWallet->withdraw($firstWallet->balance);
        self::assertEquals($firstWallet->balance, 0);

        self::assertNull($firstWallet->safeTransfer($secondWallet, 100));
        self::assertEquals($firstWallet->balance, 0);
        self::assertEquals($secondWallet->balance, 0);

        $transfer = $firstWallet->forceTransfer($secondWallet, 100);
        self::assertNotNull($transfer);
        self::assertEquals($firstWallet->balance, -100);
        self::assertEquals($secondWallet->balance, 100);
        self::assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->forceTransfer($firstWallet, 100);
        self::assertNotNull($transfer);
        self::assertEquals($firstWallet->balance, 0);
        self::assertEquals($secondWallet->balance, 0);
        self::assertEquals($transfer->status, Transfer::STATUS_TRANSFER);
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

        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $wallet->transfer($wallet, 100);
        self::assertEquals($wallet->balance, 100);

        $wallet->withdraw($wallet->balance);
        self::assertEquals($wallet->balance, 0);
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

        self::assertEquals($wallet->balance, 0);
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

        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(1);
        self::assertEquals($wallet->balance, 1);

        $wallet->withdraw(1, null, false);
        self::assertEquals($wallet->balance, 1);

        $wallet->withdraw(1);
        self::assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     */
    public function testWalletUnique(): void
    {
        if (!(app(DbService::class)->connection() instanceof PostgresConnection)) {
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
        self::assertEquals($secondWallet->getKey(), $firstWallet->getKey());

        $test2 = $user->wallets()->create([
            'name' => 'Test2'
        ]);

        self::assertEquals(
            $test2->getKey(),
            $user->getWallet('test2')->getKey()
        );

        // check default wallet
        self::assertEquals(
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
            self::assertEquals($name, $user->getWallet($name)->name);
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

        self::assertEquals($a->balance, 0);
        self::assertEquals($b->balance, 0);

        $a->deposit($product->getAmountProduct($a));
        self::assertEquals($a->balance, $product->getAmountProduct($a));

        $b->deposit($product->getAmountProduct($b));
        self::assertEquals($b->balance, $product->getAmountProduct($b));

        $transfer = $a->pay($product);
        $paidTransfer = $a->paid($product);
        self::assertTrue((bool)$paidTransfer);
        self::assertEquals($transfer->getKey(), $paidTransfer->getKey());
        self::assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        self::assertEquals($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        self::assertEquals($transfer->from->id, $a->id);
        self::assertEquals($transfer->to->id, $product->id);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);
        self::assertEquals($a->balance, 0);
        self::assertEquals($product->balance, $product->getAmountProduct($a));

        $transfer = $b->pay($product);
        $paidTransfer = $b->paid($product);
        self::assertTrue((bool)$paidTransfer);
        self::assertEquals($transfer->getKey(), $paidTransfer->getKey());
        self::assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        self::assertEquals($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        self::assertEquals($transfer->from->id, $b->id);
        self::assertEquals($transfer->to->id, $product->id);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);
        self::assertEquals($b->balance, 0);
        self::assertEquals($product->balance, $product->getAmountProduct($b) * 2);

        self::assertTrue($a->refund($product));
        self::assertEquals($product->balance, $product->getAmountProduct($a));
        self::assertEquals($a->balance, $product->getAmountProduct($a));

        self::assertTrue($b->refund($product));
        self::assertEquals($product->balance, 0);
        self::assertEquals($b->balance, $product->getAmountProduct($b));
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

        self::assertEquals($default->balance, 0);

        $transaction = $default->deposit(100);
        self::assertEquals($transaction->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transaction->amount, 100);
        self::assertEquals($default->balance, 100);

        $newWallet = $user->createWallet(['name' => 'New Wallet']);

        $transfer = $default->transfer($newWallet, 100);
        self::assertEquals($default->balance, 0);
        self::assertEquals($newWallet->balance, 100);

        self::assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);
        self::assertEquals($transfer->withdraw->amount, -100);

        self::assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transfer->deposit->amount, 100);
    }

}
