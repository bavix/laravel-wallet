<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

/**
 * @internal
 */
class BalanceLockTest extends BalanceTest
{
    use RaceCondition;
}
