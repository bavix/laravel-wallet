<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\ProxyService;
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
            '--path' => __DIR__ . '/migrations'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => dirname(__DIR__) . '/database/migrations_v1'
        ]);
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => dirname(__DIR__) . '/database/migrations_v2'
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
        // wallet
        $app['config']->set('wallet.currencies', [
            'my-usd' => 'USD',
            'my-eur' => 'EUR',
            'my-rub' => 'RUB',
            'def-curr' => 'EUR',
        ]);

        // database
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

}
