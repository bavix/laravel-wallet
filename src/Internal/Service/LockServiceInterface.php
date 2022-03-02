<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface LockServiceInterface
{
    /**
     * @return mixed
     */
    public function block(string $key, callable $callback);
}
