<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface DatabaseServiceInterface
{
    /**
     * @return mixed
     */
    public function transaction(callable $callback);
}
