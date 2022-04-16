<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;

interface LockServiceInterface
{
    /**
     * @throws LockProviderNotFoundException
     */
    public function block(string $key, callable $callback): mixed;
}
