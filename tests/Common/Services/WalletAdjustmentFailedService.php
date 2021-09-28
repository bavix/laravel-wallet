<?php

namespace Bavix\Wallet\Test\Common\Services;

use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\WalletService;
use Doctrine\DBAL\Exception\InvalidArgumentException;

class WalletAdjustmentFailedService extends WalletService
{
    /**
     * @throws InvalidArgumentException
     */
    public function adjustment(WalletModel $wallet, ?array $meta = null): void
    {
        throw new InvalidArgumentException(__METHOD__);
    }
}
