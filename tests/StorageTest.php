<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\StorageInterface;

/**
 * @internal
 */
class StorageTest extends TestCase
{
    public function testFlush(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $storage = app(StorageInterface::class);

        self::assertTrue($storage->sync('hello', 34));
        self::assertTrue($storage->sync('world', 42));
        self::assertSame('42', $storage->get('world'));
        self::assertSame('34', $storage->get('hello'));
        self::assertTrue($storage->flush());

        $storage->get('hello'); // record not found
    }
}
