<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Events\EventInterface;

/**
 * Service for dispatching events to the listeners.
 */
interface DispatcherServiceInterface
{
    /**
     * Dispatches an event to the listeners.
     *
     * This method sends the given event to all registered listeners.
     * The event object is passed to each listener's `handle` method.
     *
     * @param EventInterface $event Event object to be dispatched
     */
    public function dispatch(EventInterface $event): void;

    /**
     * Removes all events from the dispatcher.
     *
     * This method clears all events from the dispatcher. After calling this method,
     * the dispatcher will not have any events to dispatch.
     */
    public function forgot(): void;

    /**
     * Flushes all events.
     *
     * This method sends all events that have been dispatched to the listeners
     * to the listeners. After calling this method, the dispatcher will not
     * have any events to dispatch.
     */
    public function flush(): void;

    /**
     * Flushes all events, but without throwing an exception if the transaction is rolled back.
     *
     * This method flushes all events that have not been sent yet, but does not throw an exception if the transaction is rolled back.
     */
    public function lazyFlush(): void;
}
