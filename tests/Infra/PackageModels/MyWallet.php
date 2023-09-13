<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

final class MyWallet extends \Bavix\Wallet\Models\Wallet
{
    public function helloWorld(): string
    {
        return 'hello world';
    }
}
