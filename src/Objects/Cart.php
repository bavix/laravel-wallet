<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Dto\ItemDto;
use Bavix\Wallet\Internal\Dto\ItemDtoInterface;
use Bavix\Wallet\Internal\Exceptions\CartEmptyException;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use function count;
use Countable;

final class Cart implements Countable, CartInterface
{
    /**
     * @var Product[]
     */
    private array $items = [];

    /**
     * @var array<string, int>
     */
    private array $quantity = [];

    private array $meta = [];

    public function __construct(
        private CastServiceInterface $castService,
        private MathServiceInterface $math
    ) {
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function withMeta(array $meta): self
    {
        $self = clone $this;
        $self->meta = $meta;

        return $self;
    }

    /**
     * @codeCoverageIgnore
     *
     * @deprecated
     * @see withMeta
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function withItem(Product $product, int $quantity = 1): self
    {
        $self = clone $this;

        $productId = $self->productId($product);

        $self->quantity[$productId] = $self->getQuantity($product) + $quantity;
        $self->items[$productId] = $product;

        return $self;
    }

    /**
     * @codeCoverageIgnore
     *
     * @deprecated
     * @see withItem
     */
    public function addItem(Product $product, int $quantity = 1): self
    {
        $productId = $this->productId($product);

        $this->quantity[$productId] = $this->getQuantity($product) + $quantity;
        $this->items[$productId] = $product;

        return $this;
    }

    public function withItems(iterable $products): self
    {
        $self = clone $this;
        foreach ($products as $product) {
            $self = $self->withItem($product);
        }

        return $self;
    }

    /**
     * @codeCoverageIgnore
     *
     * @deprecated
     * @see withItems
     */
    public function addItems(iterable $products): self
    {
        foreach ($products as $product) {
            $this->addItem($product);
        }

        return $this;
    }

    /**
     * @return Product[]
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $count = $this->getQuantity($item);
            for ($i = 0; $i < $count; ++$i) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return Product[]
     */
    public function getUniqueItems(): array
    {
        return $this->items;
    }

    public function getTotal(Customer $customer): string
    {
        $result = 0;
        foreach ($this->items as $item) {
            $price = $this->math->mul($this->getQuantity($item), $item->getAmountProduct($customer));
            $result = $this->math->add($result, $price);
        }

        return (string) $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getQuantity(Product $product): int
    {
        return $this->quantity[$this->productId($product)] ?? 0;
    }

    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDtoInterface
    {
        $items = array_map(
            fn (Product $product): ItemDtoInterface => new ItemDto($product, $this->getQuantity($product)),
            $this->getUniqueItems()
        );

        if (count($items) === 0) {
            throw new CartEmptyException('Cart is empty', ExceptionInterface::CART_EMPTY);
        }

        return new BasketDto($items, $this->getMeta());
    }

    private function productId(Product $product): string
    {
        return $product::class.':'.$this->castService->getModel($product)->getKey();
    }
}
