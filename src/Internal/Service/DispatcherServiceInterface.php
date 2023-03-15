<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Events\EventInterface;

interface DispatcherServiceInterface
{
    public function dispatch(EventInterface $event): void;

    public function forgot(): void;

    public function flush(): void;

    public function lazyFlush(): void;
}
