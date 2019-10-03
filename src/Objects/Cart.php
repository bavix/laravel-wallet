<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Countable;
use function array_unique;
use function count;
use function get_class;

class Cart implements Countable
{

    /**
     * @var Product[]
     */
    protected $items = [];

    /**
     * @var int[]
     */
    protected $quantity = [];

    /**
     * @return static
     * @deprecated use app(Cart::class)
     * @codeCoverageIgnore
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * @param Product $product
     * @param int $quantity
     * @return static
     */
    public function addItem(Product $product, int $quantity = 1): self
    {
        $this->addQuantity($product, $quantity);
        for ($i = 0; $i < $quantity; $i++) {
            $this->items[] = $product;
        }
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
     * @return Product[]
     */
    public function getUniqueItems(): array
    {
        return array_unique($this->items);
    }

    /**
     * The method returns the transfers already paid for the goods
     *
     * @param Customer $customer
     * @param bool|null $gifts
     * @return Transfer[]
     */
    public function alreadyBuy(Customer $customer, bool $gifts = null): array
    {
        $status = [Transfer::STATUS_PAID];
        if ($gifts) {
            $status[] = Transfer::STATUS_GIFT;
        }

        /**
         * @var Transfer $query
         */
        $result = [];
        $query = $customer->transfers();
        foreach ($this->getUniqueItems() as $product) {
            $collect = (clone $query)
                ->where('to_type', $product->getMorphClass())
                ->where('to_id', $product->getKey())
                ->whereIn('status', $status)
                ->orderBy('id', 'desc')
                ->limit($this->getQuantity($product))
                ->get();

            foreach ($collect as $datum) {
                $result[] = $datum;
            }
        }

        return $result;
    }

    /**
     * @param Customer $customer
     * @param bool|null $force
     * @return bool
     */
    public function canBuy(Customer $customer, bool $force = null): bool
    {
        foreach ($this->items as $item) {
            if (!$item->canBuy($customer, $this->getQuantity($item), $force)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Customer $customer
     * @return int
     */
    public function getTotal(Customer $customer): int
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result += $item->getAmountProduct($customer);
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

    /**
     * @param Product $product
     * @return int
     */
    public function getQuantity(Product $product): int
    {
        $class = get_class($product);
        $uniq = $product->getUniqueId();
        return $this->quantity[$class][$uniq] ?? 0;
    }

    /**
     * @param Product $product
     * @param int $quantity
     */
    protected function addQuantity(Product $product, int $quantity): void
    {
        $class = get_class($product);
        $uniq = $product->getUniqueId();
        $this->quantity[$class][$uniq] = $this->getQuantity($product) + $quantity;
    }

}
