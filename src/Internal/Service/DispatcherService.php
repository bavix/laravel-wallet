<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Events\EventInterface;
use Illuminate\Contracts\Events\Dispatcher;

final class DispatcherService implements DispatcherServiceInterface
{
    /**
     * @var array<string, bool>
     */
    private array $events = [];

    public function __construct(
        private Dispatcher $dispatcher,
        private ConnectionServiceInterface $connectionService
    ) {
    }

    public function dispatch(EventInterface $event): void
    {
        $this->events[$event::class] = true;
        $this->dispatcher->push($event::class, [$event]);
    }

    public function flush(): void
    {
        foreach (array_keys($this->events) as $event) {
            $this->dispatcher->flush($event);
        }

        $this->dispatcher->forgetPushed();
        $this->events = [];
    }

    public function forgot(): void
    {
        foreach (array_keys($this->events) as $event) {
            $this->dispatcher->forget($event);
        }

        $this->events = [];
    }

    public function lazyFlush(): void
    {
        if ($this->connectionService->get()->transactionLevel() === 0) {
            $this->flush();
        }
    }
}
