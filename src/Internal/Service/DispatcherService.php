<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Events\EventInterface;
use Illuminate\Contracts\Events\Dispatcher;

final class DispatcherService implements DispatcherServiceInterface
{
    /**
     * @var string[]
     */
    private array $events = [];

    public function __construct(
        private Dispatcher $dispatcher
    ) {
    }

    public function dispatch(EventInterface $event): void
    {
        $this->events[] = $event::class;
        $this->dispatcher->push($event::class, [$event]);
    }

    public function flush(): void
    {
        foreach ($this->events as $event) {
            $this->dispatcher->flush($event);
        }
    }

    public function forgot(): void
    {
        foreach ($this->events as $event) {
            $this->dispatcher->forget($event);
        }
    }
}
