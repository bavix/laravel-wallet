<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Simple\Store;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Common\Models\Transfer;
use Bavix\Wallet\Test\Common\Models\Wallet;
use Bavix\Wallet\Test\Common\Rate;
use Bavix\Wallet\WalletServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use function app;
use function dirname;

class TestCase extends OrchestraTestCase
{

    /**
     * @return void
     */
    public function setUp(): void
    {
        app(ProxyService::class)->fresh();
        parent::setUp();
        $this->withFactories(__DIR__ . '/factories');
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => dirname(__DIR__) . '/database/migrations_v1'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => dirname(__DIR__) . '/database/migrations_v2'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => __DIR__ . '/migrations'
        ]);
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        // Bind eloquent models to IoC container
        $app['config']->set('wallet.package.rateable', Rate::class);
        $app['config']->set('wallet.package.storable', Store::class);
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
        // new table name's
        $app['config']->set('wallet.transaction.table', 'transaction');
        $app['config']->set('wallet.transfer.table', 'transfer');
        $app['config']->set('wallet.wallet.table', 'wallet');

        // override model's
        $app['config']->set('wallet.transaction.model', Transaction::class);
        $app['config']->set('wallet.transfer.model', Transfer::class);
        $app['config']->set('wallet.wallet.model', Wallet::class);

        // wallet
        $app['config']->set('wallet.currencies', [
            'my-usd' => 'USD',
            'my-eur' => 'EUR',
            'my-rub' => 'RUB',
            'def-curr' => 'EUR',
        ]);

        $app['config']->set('wallet.lock.enabled', false);

        if (extension_loaded('memcached')) {
            $app['config']->set('cache.default', 'memcached');
            $app['config']->set('wallet.lock.cache', 'memcached');
        }

        $app['config']->set('cache.stores.memcached', [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ]);

        // database
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param string $message
     */
    public function expectExceptionMessageStrict(string $message): void
    {
        $this->expectExceptionMessageRegExp("~^{$message}$~");
    }

}
