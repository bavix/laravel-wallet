<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

/**
 * @internal
 */
class CartLockTest extends CartTest
{
    use RaceCondition;
}
