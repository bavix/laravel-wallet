<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;
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
     * @var array<string, ItemDtoInterface[]>
     */
    private array $items = [];

    /**
     * @var array<mixed>
     */
    private array $meta = [];

    public function __construct(
        private CastServiceInterface $castService,
        private MathServiceInterface $math
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<mixed> $meta
     */
    public function withMeta(array $meta): self
    {
        $self = clone $this;
        $self->meta = $meta;

        return $self;
    }

    /**
     * @param positive-int $quantity
     */
    public function withItem(ProductInterface $product, int $quantity = 1, int|string|null $pricePerItem = null): self
    {
        $self = clone $this;

        $productId = $self->productId($product);

        $self->items[$productId] ??= [];
        $self->items[$productId][] = new ItemDto($product, $quantity, $pricePerItem);

        return $self;
    }

    /**
     * @param iterable<ProductInterface> $products
     */
    public function withItems(iterable $products): self
    {
        $self = clone $this;
        foreach ($products as $product) {
            $self = $self->withItem($product);
        }

        return $self;
    }

    /**
     * @return ProductInterface[]
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            foreach ($item as $datum) {
                $items[] = $datum->getItems();
            }
        }

        return array_merge(...$items);
    }

    public function getTotal(Customer $customer): string
    {
        $result = 0;
        $prices = [];
        foreach ($this->items as $productId => $_items) {
            foreach ($_items as $item) {
                $product = $item->getProduct();
                $pricePerItem = $item->getPricePerItem();
                if ($pricePerItem === null) {
                    $prices[$productId] ??= $product->getAmountProduct($customer);
                    $pricePerItem = $prices[$productId];
                }

                $price = $this->math->mul(count($item), $pricePerItem);
                $result = $this->math->add($result, $price);
            }
        }

        return (string) $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getQuantity(ProductInterface $product): int
    {
        $quantity = 0;
        $items = $this->items[$this->productId($product)] ?? [];
        foreach ($items as $item) {
            $quantity += $item->count();
        }

        return $quantity;
    }

    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDtoInterface
    {
        $items = array_merge(...array_values($this->items));

        if ($items === []) {
            throw new CartEmptyException('Cart is empty', ExceptionInterface::CART_EMPTY);
        }

        return new BasketDto($items, $this->getMeta());
    }

    private function productId(ProductInterface $product): string
    {
        return $product::class . ':' . $this->castService->getModel($product)->getKey();
    }
}
