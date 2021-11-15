<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

interface AtomicServiceInterface
{
    /** @return mixed */
    public function block(object $object, callable $closure);
}
