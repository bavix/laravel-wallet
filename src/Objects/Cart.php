<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Countable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use function count;

class Cart implements Countable
{

    /**
     * @var Product[]
     */
    protected $items = [];

    /**
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * @param Product $product
     * @return static
     */
    public function addItem(Product $product): self
    {
        $this->items[] = $product;
        return $this;
    }

    /**
     * @param iterable $products
     * @return static
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
        return $this->items;
    }

    /**
     *
     *
     * @param Customer $customer
     * @param bool|null $gifts
     * @return Transfer[]
     */
    public function alreadyBuy(Customer $customer, bool $gifts = null): array
    {
        $results = [];
        foreach ($this->getItems() as $item) {
            $transfer = $customer->paid($item, $gifts);
            $results[] = $transfer;
        }

        return $results;
    }

    /**
     * @param Customer $customer
     * @param bool|null $force
     * @return bool
     */
    public function canBuy(Customer $customer, bool $force = null): bool
    {
        foreach ($this->items as $item) {
            if (!$item->canBuy($customer, $force)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result += $item->getAmountProduct();
        }
        return $result;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

}
