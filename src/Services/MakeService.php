<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Objects\Operation;

class MakeService
{

    /**
     * @return Bring
     */
    public function makeBring(): Bring
    {
        return new Bring();
    }

    public function makeCart(): Cart
    {
        return new Cart();
    }

    /**
     * @return EmptyLock
     */
    public function makeEmptyLock(): EmptyLock
    {
        return new EmptyLock();
    }

    /**
     * @return Operation
     */
    public function makeOperation(): Operation
    {
        return new Operation();
    }

}
