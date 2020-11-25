<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

class BalanceLockTest extends BalanceTest
{
    use RaceCondition;
}
