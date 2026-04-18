<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Events\EventInterface;
use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Internal\Service\DispatcherService;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @internal
 */
final class DispatcherServiceTest extends TestCase
{
    public function testDispatchNowDelegatesToIlluminateDispatcher(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $connectionService = $this->createMock(ConnectionServiceInterface::class);
        $event = $this->createMock(EventInterface::class);

        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with($event);

        $service = new DispatcherService($dispatcher, $connectionService);
        $service->dispatchNow($event);
    }
}
