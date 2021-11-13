<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Service\UuidServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Factories\ItemFactory;
use Bavix\Wallet\Test\Factories\UserCashierFactory;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\UserCashier;
use Bavix\Wallet\Test\Models\UserMulti;
use function compact;
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
        self::assertSame($user->wallet->id, $wallet->id);
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
            self::assertSame($slug, $wallet->slug);

            self::assertTrue((bool) $wallet->deposit(1000));
        }

        self::assertEqualsCanonicalizing(
            $slugs,
            $user->wallets->pluck('slug')->toArray()
        );

        self::assertCount(count($slugs), $user->wallets()->get());

        foreach ($user->wallets()->get() as $wallet) {
            self::assertSame(1000, $wallet->balanceInt);
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
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 0);

        $wallet->deposit(10);
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 10);

        $wallet->deposit(125);
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 135);

        $wallet->deposit(865);
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 1000);

        self::assertSame($user->transactions()->count(), 3);

        $wallet->withdraw($wallet->balanceInt);
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 0);

        $transaction = $wallet->depositFloat(10.10);
        self::assertSame($user->balanceInt, 0);
        self::assertSame($wallet->balanceInt, 1010);
        self::assertSame((float) $wallet->balanceFloat, 10.10);

        $user->refresh();

        // is equal
        self::assertTrue($transaction->wallet->is($user->getWallet('deposit')));
        self::assertTrue($user->getWallet('deposit')->is($wallet));
        self::assertTrue($wallet->is($user->getWallet('deposit')));

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertSame((float) $wallet->balanceFloat, 0.);
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

        self::assertSame(100.1, (float) $wallet->balanceFloat);
        self::assertSame(10010, $wallet->balanceInt);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertSame(0., (float) $wallet->balanceFloat);

        // find
        $userFind = UserMulti::query()->find($userInit->id); // refresh
        self::assertTrue($userInit->is($userFind));
        self::assertTrue($userFind->hasWallet((string) $userInit->getKey()));

        $wallet = $userFind->getWallet((string) $userInit->getKey());
        $wallet->depositFloat(100.1);

        self::assertSame(100.1, (float) $wallet->balanceFloat);
        self::assertSame(10010, $wallet->balanceInt);

        $wallet->withdrawFloat($wallet->balanceFloat);
        self::assertSame(0., (float) $wallet->balanceFloat);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/286#issue-750353538
     */
    public function testGetWalletOrFail(): void
    {
        /** @var UserMulti $userMulti */
        $userMulti = UserMultiFactory::new()->create();
        self::assertSame(0, $userMulti->balanceInt); // createWallet
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
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);

        /** @var UserMulti $userMulti */
        $userMulti = UserMultiFactory::new()->create();
        $userMulti
            ->getWalletOrFail(config('wallet.wallet.default.slug', 'default'))
        ;
    }

    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::AMOUNT_INVALID);
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
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertSame(0, $wallet->balanceInt);

        $wallet->deposit(100);
        self::assertSame(100, $wallet->balanceInt);

        $wallet->withdraw(10);
        self::assertSame(90, $wallet->balanceInt);

        $wallet->withdraw(81);
        self::assertSame(9, $wallet->balanceInt);

        $wallet->withdraw(9);
        self::assertSame(0, $wallet->balanceInt);

        $wallet->withdraw(1);
    }

    public function testInvalidWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
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

        self::assertNotSame($first->id, $second->id);
        self::assertNotSame($firstWallet->id, $secondWallet->id);
        self::assertSame(0, $firstWallet->balanceInt);
        self::assertSame(0, $secondWallet->balanceInt);

        $firstWallet->deposit(100);
        self::assertSame(100, $firstWallet->balanceInt);

        $secondWallet->deposit(100);
        self::assertSame(100, $secondWallet->balanceInt);

        $transfer = $firstWallet->transfer($secondWallet, 100);
        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $firstWallet->balanceInt);
        self::assertSame(0, $second->balanceInt);
        self::assertSame(200, $secondWallet->balanceInt);
        self::assertSame(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertSame(100, $secondWallet->balanceInt);
        self::assertSame(100, $firstWallet->balanceInt);
        self::assertSame(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        self::assertSame(0, $secondWallet->balanceInt);
        self::assertSame(200, $firstWallet->balanceInt);
        self::assertSame(Transfer::STATUS_TRANSFER, $transfer->status);

        $firstWallet->withdraw($firstWallet->balanceInt);
        self::assertSame(0, $firstWallet->balanceInt);

        self::assertNull($firstWallet->safeTransfer($secondWallet, 100));
        self::assertSame(0, $firstWallet->balanceInt);
        self::assertSame(0, $secondWallet->balanceInt);

        $transfer = $firstWallet->forceTransfer($secondWallet, 100);
        self::assertNotNull($transfer);
        self::assertSame(-100, $firstWallet->balanceInt);
        self::assertSame(100, $secondWallet->balanceInt);
        self::assertSame(Transfer::STATUS_TRANSFER, $transfer->status);

        $transfer = $secondWallet->forceTransfer($firstWallet, 100);
        self::assertNotNull($transfer);
        self::assertSame(0, $firstWallet->balanceInt);
        self::assertSame(0, $secondWallet->balanceInt);
        self::assertSame(Transfer::STATUS_TRANSFER, $transfer->status);
    }

    public function testTransferYourself(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertSame($wallet->balanceInt, 0);

        $wallet->deposit(100);
        $wallet->transfer($wallet, 100);
        self::assertSame($wallet->balanceInt, 100);

        $wallet->withdraw($wallet->balanceInt);
        self::assertSame($wallet->balanceInt, 0);
    }

    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertSame($wallet->balanceInt, 0);
        $wallet->withdraw(1);
    }

    public function testConfirmed(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet = $user->createWallet([
            'name' => 'deposit',
        ]);

        self::assertSame($wallet->balanceInt, 0);

        $wallet->deposit(1);
        self::assertSame($wallet->balanceInt, 1);

        $wallet->withdraw(1, null, false);
        self::assertSame($wallet->balanceInt, 1);

        $wallet->withdraw(1);
        self::assertSame($wallet->balanceInt, 0);
    }

    /**
     * @throws
     */
    public function testWalletUnique(): void
    {
        $this->expectException(QueryException::class);

        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();

        $user->createWallet(['name' => 'deposit']);
        $user->createWallet(['name' => 'deposit']);
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
        self::assertSame($secondWallet->getKey(), $firstWallet->getKey());

        $test2 = $user->wallets()->create([
            'name' => 'Test2',
            'uuid' => app(UuidServiceInterface::class)->uuid4(),
        ]);

        self::assertSame(
            $test2->getKey(),
            $user->getWallet('test2')->getKey()
        );

        // check default wallet
        self::assertSame(
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
            self::assertSame($name, $user->getWallet($name)->name);
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

        self::assertSame($a->balanceInt, 0);
        self::assertSame($b->balanceInt, 0);

        $a->deposit($product->getAmountProduct($a));
        self::assertSame($a->balanceInt, $product->getAmountProduct($a));

        $b->deposit($product->getAmountProduct($b));
        self::assertSame($b->balanceInt, $product->getAmountProduct($b));

        $transfer = $a->pay($product);
        $paidTransfer = $a->paid($product);
        self::assertTrue((bool) $paidTransfer);
        self::assertSame($transfer->getKey(), $paidTransfer->getKey());
        self::assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        self::assertSame($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        self::assertSame($transfer->from->id, $a->id);
        self::assertSame($transfer->to->id, $product->id);
        self::assertSame($transfer->status, Transfer::STATUS_PAID);
        self::assertSame($a->balanceInt, 0);
        self::assertSame($product->balanceInt, $product->getAmountProduct($a));

        $transfer = $b->pay($product);
        $paidTransfer = $b->paid($product);
        self::assertTrue((bool) $paidTransfer);
        self::assertSame($transfer->getKey(), $paidTransfer->getKey());
        self::assertInstanceOf(UserMulti::class, $paidTransfer->withdraw->payable);
        self::assertSame($user->getKey(), $paidTransfer->withdraw->payable->getKey());
        self::assertSame($transfer->from->id, $b->id);
        self::assertSame($transfer->to->id, $product->id);
        self::assertSame($transfer->status, Transfer::STATUS_PAID);
        self::assertSame($b->balanceInt, 0);
        self::assertSame($product->balanceInt, $product->getAmountProduct($b) * 2);

        self::assertTrue($a->refund($product));
        self::assertSame($product->balanceInt, $product->getAmountProduct($a));
        self::assertSame($a->balanceInt, $product->getAmountProduct($a));

        self::assertTrue($b->refund($product));
        self::assertSame($product->balanceInt, 0);
        self::assertSame($b->balanceInt, $product->getAmountProduct($b));
    }

    public function testUserCashier(): void
    {
        /** @var UserCashier $user */
        $user = UserCashierFactory::new()->create();
        $default = $user->wallet;

        self::assertSame($default->balanceInt, 0);

        $transaction = $default->deposit(100);
        self::assertSame($transaction->type, Transaction::TYPE_DEPOSIT);
        self::assertSame($transaction->amountInt, 100);
        self::assertSame($default->balanceInt, 100);

        $newWallet = $user->createWallet(['name' => 'New Wallet']);

        $transfer = $default->transfer($newWallet, 100);
        self::assertSame($default->balanceInt, 0);
        self::assertSame($newWallet->balanceInt, 100);

        self::assertSame($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);
        self::assertSame($transfer->withdraw->amountInt, -100);

        self::assertSame($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertSame($transfer->deposit->amountInt, 100);
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
        self::assertSame($user->wallet->id, $wallet->id);

        $user->deposit(1000000000);
        self::assertSame(1000., (float) $wallet->balanceFloat);
    }
}
