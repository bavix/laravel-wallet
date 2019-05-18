<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;

class Cart
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
     * @return Product[]
     */
    public function getItems(): array
    {
        return $this->items;
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
     * @return array|null
     */
    public function getMeta(): ?array
    {
        $meta = [];
        foreach ($this->items as $item) {
            $data = $item->getMetaProduct();
            if ($data) {
                $meta[] = $data;
            }
        }

        if (empty($meta)) {
            return null;
        }

        return $meta;
    }

}
