<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

interface AtomicServiceInterface
{
    /**
     * @return mixed
     */
    public function block(Wallet $object, callable $callback);
}
