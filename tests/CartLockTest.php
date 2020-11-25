<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

class CartLockTest extends CartTest
{
    use RaceCondition;
}
