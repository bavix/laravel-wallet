<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Events\EventInterface;
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\UnknownEventException;
use Illuminate\Contracts\Events\Dispatcher;

final class DispatcherService implements DispatcherServiceInterface
{
    private Dispatcher $dispatcher;

    /** @var string[] */
    private array $events = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(EventInterface $event): void
    {
        $name = $this->getEventName($event);
        $this->events[] = $name;
        $this->dispatcher->push($name, [$event]);
    }

    public function flush(): void
    {
        foreach ($this->events as $event) {
            $this->dispatcher->flush($event);
        }

        $this->dispatcher->forgetPushed();
        $this->events = [];
    }

    public function forgot(): void
    {
        foreach ($this->events as $event) {
            $this->dispatcher->forget($event);
        }

        $this->events = [];
    }

    /** @throws UnknownEventException */
    private function getEventName(EventInterface $event): string
    {
        if ($event instanceof BalanceUpdatedEventInterface) {
            return BalanceUpdatedEventInterface::class;
        }

        if ($event instanceof WalletCreatedEventInterface) {
            return WalletCreatedEventInterface::class;
        }

        throw new UnknownEventException(
            'Unknown event '.get_class($event),
            ExceptionInterface::UNKNOWN_EVENT
        );
    }
}
