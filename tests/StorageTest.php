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

        self::assertTrue($storage->sync('hello', 'world'));
        self::assertTrue($storage->sync('world', 'hello'));
        self::assertSame('hello', $storage->get('world'));
        self::assertSame('world', $storage->get('hello'));
        self::assertTrue($storage->flush());

        $storage->get('hello'); // record not found
    }
}
