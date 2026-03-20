<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra;

use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Bavix\Wallet\Test\Infra\PackageModels\WalletState;
use Illuminate\Foundation\Application;
use Override;

abstract class ProjectionTestCase extends TestCase
{
    /**
     * @param Application $app
     * @return non-empty-array<int, string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        $providers = parent::getPackageProviders($app);

        $app['config']->set('wallet.transaction.model', TransactionState::class);
        $app['config']->set('wallet.wallet.model', WalletState::class);

        $providers[] = ProjectionTestServiceProvider::class;

        return $providers;
    }
}
