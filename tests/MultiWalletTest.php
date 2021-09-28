<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Test\Factories\ItemFactory;
use Bavix\Wallet\Test\Factories\UserCashierFactory;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\UserCashier;
use Bavix\Wallet\Test\Models\UserMulti;
use function compact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use function range;
use Throwable;

/**
 * @internal
 */
class MultiWalletTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $slug = config('wallet.wallet.default.slug', 'default');

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        self::assertNull($user->getWallet($slug));

        $wallet = $user->createWallet(['name' => 'Simple', 'slug' => $slug]);
        self::assertNotNull($wallet);
        self::assertNotNull($user->wallet);
        self::assertEquals($user->wallet->id, $wallet->id);
    }

    public function testOnlyCreatedWallets(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $slugs = ['dollar', 'euro', 'ruble'];

        foreach ($slugs as $slug) {
            self::assertNull($user->getWallet($slug));
            $wallet = $user->createWallet([
                'name' => ucfirst($slug),
                'slug' => $slug,
            ]);

            self::assertNotNull($wallet);
            self::assertEquals($slug, $wallet->slug);

            self::assertTrue((bool) $wallet->deposit(1000));
        }

        self::assertEqualsCanonicalizing(
            $slugs,
            $user->wallets->pluck('slug')->toArray()
        );

        self::assertCount(count($slugs), $user->wallets()->get());

        foreach ($user->wallets()->get() as $wallet) {
            self::assertEquals(1000, $wallet->balance);
            self::assertContains($wallet->slug, $slugs);
        }
    }

    public function testDeposit(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        self::assertFalse($user->hasWallet('deposit'));
        $wallet = $user->createWallet([
            'name' => 'Deposit',
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

    public function testDepositFloat(): void
    {
        /**
         * @var UserMulti $userInit
         * @var UserMulti $userFind
         */
        $userInit = UserMultiFactory::new()->create();
        $wallet = $userInit->createWallet([
            'name' => 'my-simple-wallet',
            'slug' => $userInit->getKey(),
        ]);

        // without find
        $wallet->depositFloat(100.1);

        self::assertEquals(100.1, $wallet->balanceFloat);
        self::assertEquals(10010, $wallet->balance);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertEquals(0, $wallet->balanceFloat);

        // find
        $userFind = UserMulti::query()->find($userInit->id); // refresh
        self::assertTrue($userInit->is($userFind));
        self::assertTrue($userFind->hasWallet($userInit->getKey()));

        $wallet = $userFind->getWallet($userInit->getKey());
        $wallet->depositFloat(100.1);

        self::assertEquals(100.1, $wallet->balanceFloat);
        self::assertEquals(10010, $wallet->balance);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertEquals(0, $wallet->balanceFloat);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/286#issue-750353538
     */
    public function testGetWalletOrFail(): void
    {
        /** @var UserMulti $userMulti */
        $userMulti = UserMultiFactory::new()->create();
        self::assertEquals(0, $userMulti->balance); // createWallet
        $userMulti
            ->getWalletOrFail(config('wallet.wallet.default.slug', 'default'))
        ;
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/286#issue-750353538
     */
    public function testTransferWalletNotExists(): void
    {
        $this->expectException(ModelNotFoundException::class);

        /** @var UserMulti $userMulti */
        $userMulti = UserMultiFactory::new()->create();
        $userMulti
            ->getWalletOrFail(config('wallet.wallet.default.slug', 'default'))
        ;
    }

    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        $wallet->deposit(-1);
    }

    public function testWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertEquals(0, $wallet->balance);

        $wallet->deposit(100);
        self::assertEquals(100, $wallet->balance);

        $wallet->withdraw(10);
        self::assertEquals(90, $wallet->balance);

        $wallet->withdraw(81);
        self::assertEquals(9, $wallet->balance);

        $wallet->withdraw(9);
        self::assertEquals(0, $wallet->balance);

        $wallet->withdraw(1);
    }

    public function testInvalidWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        $wallet->withdraw(-1);
    }

    public function testTransfer(): void
    {
        /**
         * @var UserMulti $first
         * @var UserMulti $second
         */
        [$first, $second] = UserMultiFactory::times(2)->create();
        $firstWallet = $first->createWallet([
            'name' => 'deposit',
        ]);

        $secondWallet = $second->createWallet([
            'name' => 'deposit',
        ]);

        self::assertNotEquals($first->id, $second->id);
        self::assertNotEquals($firstWallet->id, $secondWallet->id);
        self::assertEquals(0, $firstWallet->balance);
        self::assertEquals(0, $secondWallet->balance);

        $firstWallet->deposit(100);
        self::assertEquals(100, $firstWallet->balance);

        $secondWallet->deposit(100);
        self::assertEquals(100, $secondWallet->balance);

        $transfer = $firstWallet->transfer($secondWallet, 100);
        self::assertEquals(0, $first->balance);
        self::assertEquals(0, $firstWallet->balance);
        self::assertEquals(0, $second->balance);
        self::assertEquals(200, $secondWallet->balance);
        self::assertEquals(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertEquals(100, $secondWallet->balance);
        self::assertEquals(100, $firstWallet->balance);
        self::assertEquals(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertEquals(0, $secondWallet->balance);
        self::assertEquals(200, $firstWallet->balance);
        self::assertEquals(Transfer::STATUS_TRANSFER, $transfer->status);

        $firstWallet->withdraw($firstWallet->balance);
        self::assertEquals(0, $firstWallet->balance);

        self::assertNull($firstWallet->safeTransfer($secondWallet, 100));
        self::assertEquals(0, $firstWallet->balance);
        self::assertEquals(0, $secondWallet->balance);

        $transfer = $firstWallet->forceTransfer($secondWallet, 100);
        self::assertNotNull($transfer);
        self::assertEquals(-100, $firstWallet->balance);
        self::assertEquals(100, $secondWallet->balance);
        self::assertEquals(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->forceTransfer($firstWallet, 100);
        self::assertNotNull($transfer);
        self::assertEquals(0, $firstWallet->balance);
        self::assertEquals(0, $secondWallet->balance);
        self::assertEquals(Transfer::STATUS_TRANSFER, $transfer->status);
    }

    public function testTransferYourself(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $wallet->transfer($wallet, 100);
        self::assertEquals($wallet->balance, 100);

        $wallet->withdraw($wallet->balance);
        self::assertEquals($wallet->balance, 0);
    }

    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertEquals($wallet->balance, 0);
        $wallet->withdraw(1);
    }

    public function testConfirmed(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
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
     * @throws
     */
    public function testWalletUnique(): void
    {
        $this->expectException(QueryException::class);

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();

        $user->createWallet([
            'name' => 'deposit',
        ]);

        if (app(DbService::class)->connection() instanceof PostgresConnection) {
            // enable autocommit for pgsql
            app(DbService::class)
                ->connection()
                ->commit()
            ;
        }

        $user->createWallet([
            'name' => 'deposit',
        ]);
    }

    public function testGetWallet(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();

        $firstWallet = $user->createWallet([
            'name' => 'My Test',
            'slug' => 'test',
        ]);

        $secondWallet = $user->getWallet('test');
        self::assertEquals($secondWallet->getKey(), $firstWallet->getKey());

        $test2 = $user->wallets()->create([
            'name' => 'Test2',
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

    public function testGetWalletOptimize(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $names = range('a', 'z');
        foreach ($names as $name) {
            $user->createWallet(compact('name'));
        }

        $user->load('wallets'); // optimize

        foreach ($names as $name) {
            self::assertEquals($name, $user->getWallet($name)->name);
        }
    }

    public function testPay(): void
    {
        /**
         * @var UserMulti $user
         * @var Item      $product
         */
        $user = UserMultiFactory::new()->create();
        $a = $user->createWallet(['name' => 'a']);
        $b = $user->createWallet(['name' => 'b']);

        $product = ItemFactory::new()->create([
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
        self::assertTrue((bool) $paidTransfer);
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
        self::assertTrue((bool) $paidTransfer);
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

    public function testUserCashier(): void
    {
        /** @var UserCashier $user */
        $user = UserCashierFactory::new()->create();
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

    /**
     * @throws Throwable
     */
    public function testDecimalPlaces(): void
    {
        $slug = config('wallet.wallet.default.slug', 'default');

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        self::assertNull($user->getWallet($slug));

        $wallet = $user->createWallet(['name' => 'Simple', 'slug' => $slug, 'decimal_places' => 6]);
        self::assertNotNull($wallet);
        self::assertNotNull($user->wallet);
        self::assertEquals($user->wallet->id, $wallet->id);

        $user->deposit(1000000000);
        self::assertEquals(1000, $wallet->balanceFloat);
    }
}
