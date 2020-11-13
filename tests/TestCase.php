<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Simple\BrickMath;
use Bavix\Wallet\Simple\Store;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Common\Models\Transfer;
use Bavix\Wallet\Test\Common\Models\Wallet;
use Bavix\Wallet\Test\Common\Rate;
use Bavix\Wallet\WalletServiceProvider;
use function dirname;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--path' => dirname(__DIR__).'/database/migrations_v1',
        ]);
        $this->loadMigrationsFrom([
            '--path' => dirname(__DIR__).'/database/migrations_v2',
        ]);
        $this->loadMigrationsFrom([
            '--path' => __DIR__.'/migrations',
        ]);

        app(Storable::class)->fresh();
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
        $app['config']->set('wallet.package.mathable', BrickMath::class);

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

        if (env('MEMCACHED_ENABLE')) {
            $app['config']->set('cache.default', 'memcached');
            $app['config']->set('wallet.lock.cache', 'memcached');
        }

        if (env('REDIS_ENABLE')) {
            $app['config']->set('cache.default', 'redis');
            $app['config']->set('wallet.lock.cache', 'redis');
        }

        $app['config']->set('database.connections.testing.prefix', 'tests');
        $app['config']->set('database.connections.mysql.prefix', 'tests');
    }

    /**
     * @param string $message
     */
    public function expectExceptionMessageStrict(string $message): void
    {
        $this->expectExceptionMessageMatches("~^{$message}$~");
    }
}
