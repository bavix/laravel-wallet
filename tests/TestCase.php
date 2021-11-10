<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\StorageInterface;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Common\Models\Transfer;
use Bavix\Wallet\Test\Common\Models\Wallet;
use Bavix\Wallet\Test\Common\MyExchange;
use Bavix\Wallet\Test\Common\WalletServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * @internal
 */
class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function expectExceptionMessageStrict(string $message): void
    {
        $this->expectExceptionMessageMatches("~^{$message}$~");
    }

    /**
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        $this->updateConfig($app);

        return [WalletServiceProvider::class];
    }

    protected function updateConfig(Application $app): void
    {
        /** @var Repository $config */
        $config = $app['config'];

        // Bind eloquent models to IoC container
        $config->set('wallet.services.exchange', MyExchange::class);

        // database
        $config->set('database.connections.testing.prefix', 'tests');
        $config->set('database.connections.pgsql.prefix', 'tests');
        $config->set('database.connections.mysql.prefix', 'tests');

        $mysql = $config->get('database.connections.mysql');
        $mariadb = array_merge($mysql, ['port' => 3307]);
        $percona = array_merge($mysql, ['port' => 3308]);

        $config->set('database.connections.mariadb', $mariadb);
        $config->set('database.connections.percona', $percona);

        // new table name's
        $config->set('wallet.transaction.table', 'transaction');
        $config->set('wallet.transfer.table', 'transfer');
        $config->set('wallet.wallet.table', 'wallet');

        // override model's
        $config->set('wallet.transaction.model', Transaction::class);
        $config->set('wallet.transfer.model', Transfer::class);
        $config->set('wallet.wallet.model', Wallet::class);

        $config->set('wallet.cache.driver', $config->get('cache.driver'));
        $config->set('wallet.lock.driver', $config->get('cache.driver'));
        $config->set('wallet.lock.seconds', 3);
    }
}
