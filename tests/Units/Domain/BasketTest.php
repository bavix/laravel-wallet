<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\Dto\ItemDto;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class BasketTest extends TestCase
{
    public function testCount(): void
    {
        $item = new Item();
        $productDto1 = new ItemDto($item, 24);
        $productDto2 = new ItemDto($item, 26);
        $basket = new BasketDto([$productDto1, $productDto2], []);

        self::assertEmpty($basket->meta());
        self::assertSame(2, $basket->count());

        $items = $basket->items();
        self::assertNotFalse(current($items));
        self::assertSame(0, key($items));
        self::assertSame(24, current($items)->count());
        self::assertNotFalse(next($items));
        self::assertSame(26, current($items)->count());
        self::assertSame(1, key($items));
    }

    public function testMeta(): void
    {
        $basket1 = new BasketDto([], []);
        self::assertEmpty($basket1->meta());

        $basket2 = new BasketDto([], ['hello' => 'world']);
        self::assertSame(['hello' => 'world'], $basket2->meta());
    }

    public function testEmpty(): void
    {
        $basket = new BasketDto([], []);
        self::assertEmpty($basket->items());
        self::assertEmpty($basket->meta());
        self::assertSame(0, $basket->count());
    }
}
