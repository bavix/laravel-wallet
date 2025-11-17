<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;
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

    /**
     * @var array<mixed>|null
     */
    private ?array $extra = null;

    public function __construct(
        private readonly CastServiceInterface $castService,
        private readonly MathServiceInterface $math
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
     * @return array<mixed>|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * @param array<mixed> $extra
     */
    public function withExtra(array $extra): self
    {
        $self = clone $this;
        $self->extra = $extra;

        return $self;
    }

    /**
     * @param positive-int $quantity
     */
    public function withItem(
        ProductInterface $product,
        int $quantity = 1,
        int|string|null $pricePerItem = null,
        ?Wallet $receiving = null,
    ): self {
        $self = clone $this;

        $productId = $self->productId($product);

        $self->items[$productId] ??= [];
        $self->items[$productId][] = new ItemDto($product, $quantity, $pricePerItem, $receiving);

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
                /** @var ProductInterface[] $datumItems */
                $datumItems = $datum->getItems();
                $items[] = $datumItems;
            }
        }

        /** @var array<ProductInterface[]> $items */
        return array_merge(...$items);
    }

    public function getTotal(Customer $customer): string
    {
        /** @var non-empty-string $result */
        $result = '0';
        $prices = [];
        foreach ($this->items as $productId => $_items) {
            foreach ($_items as $item) {
                $product = $item->getProduct();
                $pricePerItem = $item->getPricePerItem();
                if ($pricePerItem === null) {
                    /** @var non-empty-string $productPrice */
                    $productPrice = $product->getAmountProduct($customer);
                    $prices[$productId] ??= $productPrice;
                    /** @var int|non-empty-string $pricePerItem */
                    $pricePerItem = $prices[$productId];
                }

                /** @var int<0, max> $itemCount */
                $itemCount = count($item);
                /** @var int|non-empty-string $pricePerItemValue */
                $pricePerItemValue = $pricePerItem;
                $price = $this->math->mul($itemCount, $pricePerItemValue);
                $newResult = $this->math->add($result, $price);
                $result = $newResult;
            }
        }

        return $result;
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
        /** @var array<ItemDtoInterface[]> $itemsValues */
        $itemsValues = array_values($this->items);
        /** @var array<ItemDtoInterface> $items */
        $items = array_merge(...$itemsValues);

        if ($items === []) {
            throw new CartEmptyException('Cart is empty', ExceptionInterface::CART_EMPTY);
        }

        return new BasketDto($items, $this->meta, $this->extra);
    }

    private function productId(ProductInterface $product): string
    {
        return $product::class.':'.$this->castService->getModel($product)->getKey();
    }
}
