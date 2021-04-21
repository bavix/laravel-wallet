<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use function array_unique;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use function count;
use Countable;
use function get_class;

/** @deprecated  */
class Cart implements Countable
{
    protected Mathable $math;

    /** @var Product[] */
    protected array $items = [];

    /** @var int[] */
    protected array $quantity = [];

    protected array $meta = [];

    public function __construct(
        Mathable $math
    ) {
        $this->math = $math;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function addItem(Product $product, int $quantity = 1): self
    {
        $this->addQuantity($product, $quantity); // fixme: needle?
        for ($i = 0; $i < $quantity; ++$i) {
            $this->items[] = $product;
        }

        return $this;
    }

    /** @param Product[] $products */
    public function addItems(iterable $products): self
    {
        foreach ($products as $product) {
            $this->addItem($product);
        }

        return $this;
    }

    /** @return Product[] */
    public function getItems(): array
    {
        return $this->items;
    }

    /** @return Product[] */
    public function getUniqueItems(): array
    {
        return array_unique($this->items);
    }

    /**
     * The method returns the transfers already paid for the goods.
     *
     * @return Transfer[]
     */
    public function alreadyBuy(Customer $customer, bool $gifts = false): array
    {
        $status = [Transfer::STATUS_PAID];
        if ($gifts) {
            $status[] = Transfer::STATUS_GIFT;
        }

        /** @var Transfer $query */
        $query = $customer->transfers();

        $result = [];
        foreach ($this->getUniqueItems() as $product) {
            $collect = (clone $query)
                ->where('to_type', $product->getMorphClass())
                ->where('to_id', $product->getKey())
                ->whereIn('status', $status)
                ->orderBy('id', 'desc')
                ->limit($this->getQuantity($product))
                ->get()
            ;

            foreach ($collect as $datum) {
                $result[] = $datum;
            }
        }

        return $result;
    }

    public function canBuy(Customer $customer, bool $force = false): bool
    {
        foreach ($this->items as $item) {
            if (!$item->canBuy($customer, $this->getQuantity($item), $force)) {
                return false;
            }
        }

        return true;
    }

    public function getTotal(Customer $customer): string
    {
        $result = '0'; // fasted
        foreach ($this->items as $item) {
            $result = $this->math->add(
                $result,
                $item->getAmountProduct($customer)
            );
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getQuantity(Product $product): int
    {
        $class = get_class($product);
        $uniq = $product->getUniqueId();

        return (int) ($this->quantity[$class][$uniq] ?? 0);
    }

    protected function addQuantity(Product $product, int $quantity): void
    {
        $class = get_class($product);
        $uniq = $product->getUniqueId();

        $this->quantity[$class][$uniq] = (int) $this->math->add(
            (string) $this->getQuantity($product),
            (string) $quantity,
        );
    }
}
