<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Events\EventInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\UnknownEventException;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class DispatcherTest extends TestCase
{
    public function testUnknownEventException(): void
    {
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionCode(ExceptionInterface::UNKNOWN_EVENT);

        $dispatcher = app(DispatcherServiceInterface::class);
        $dispatcher->dispatch(new class() implements EventInterface {
        });
    }
}
