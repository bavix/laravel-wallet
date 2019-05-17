<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CartService;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\WalletServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    /**
     * @return void
     */
    public function setUp(): void
    {
        \app(ProxyService::class)->fresh();
        parent::setUp();
        $this->withFactories(__DIR__ . '/factories');
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => __DIR__ . '/migrations'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => \dirname(__DIR__) . '/database/migrations_v1'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => \dirname(__DIR__) . '/database/migrations_v2'
        ]);
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [WalletServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Bind eloquent models to IoC container
        $app->singleton(Transaction::class, config('wallet.transaction.model'));
        $app->singleton(Transfer::class, config('wallet.transfer.model'));
        $app->singleton(Wallet::class, config('wallet.wallet.model'));
        $app->singleton(CartService::class, config('wallet.services.cart'));
        $app->singleton(CommonService::class, config('wallet.services.common'));
        $app->singleton(ProxyService::class, config('wallet.services.proxy'));
        $app->singleton(WalletService::class, config('wallet.services.wallet'));

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

}
