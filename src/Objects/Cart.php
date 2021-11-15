<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use function array_unique;
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
use function get_class;

final class Cart implements Countable, CartInterface
{
    /**
     * @var Product[]
     */
    private array $items = [];

    /** @var array<string, string> */
    private array $quantity = [];

    private array $meta = [];

    private CastServiceInterface $castService;

    private MathServiceInterface $math;

    public function __construct(
        CastServiceInterface $castService,
        MathServiceInterface $math
    ) {
        $this->castService = $castService;
        $this->math = $math;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function addItem(Product $product, int $quantity = 1): self
    {
        $this->addQuantity($product, $quantity);
        $products = array_fill(0, $quantity, $product);
        $this->items = array_merge($this->items, $products);

        return $this;
    }

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
        return $this->items;
    }

    /**
     * @return Product[]
     */
    public function getUniqueItems(): array
    {
        return array_unique($this->items);
    }

    public function getTotal(Customer $customer): string
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result = $this->math->add($result, $item->getAmountProduct($customer));
        }

        return (string) $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getQuantity(Product $product): int
    {
        $model = $this->castService->getModel($product);

        return (int) ($this->quantity[get_class($product).':'.$model->getKey()] ?? 0);
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
            throw new CartEmptyException(
                'Cart is empty',
                ExceptionInterface::CART_EMPTY
            );
        }

        return new BasketDto($items, $this->getMeta());
    }

    private function addQuantity(Product $product, int $quantity): void
    {
        $model = $this->castService->getModel($product);

        $this->quantity[get_class($product).':'.$model->getKey()] = $this->math
            ->add($this->getQuantity($product), $quantity)
        ;
    }
}
