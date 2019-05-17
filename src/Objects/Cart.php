<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;

class Cart implements \Bavix\Wallet\Interfaces\Cart
{

    /**
     * @var Product[]
     */
    protected $items = [];

    /**
     * @param Product $product
     * @return Cart
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
            if (!$item->canBuy($customer)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * @return int
     */
    public function getAmountProduct(): int
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
    public function getMetaProduct(): ?array
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
