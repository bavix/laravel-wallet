<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use function array_unique;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\BasketInterface;
use Bavix\Wallet\Internal\CartInterface;
use Bavix\Wallet\Internal\Dto\AvailabilityDto;
use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\Dto\ProductDto;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transfer;
use function count;
use Countable;
use function get_class;

class Cart implements Countable, CartInterface
{
    /**
     * @var Product[]
     */
    protected array $items = [];

    /**
     * @var int[]
     */
    protected array $quantity = [];

    protected array $meta = [];

    private BasketInterface $basket;

    public function __construct(BasketInterface $basket)
    {
        $this->basket = $basket;
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

    /**
     * @return static
     */
    public function addItem(Product $product, int $quantity = 1): self
    {
        $this->addQuantity($product, $quantity);
        for ($i = 0; $i < $quantity; ++$i) {
            $this->items[] = $product;
        }

        return $this;
    }

    /**
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
        $result = [];
        $query = $customer->transfers();
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

    /**
     * @deprecated
     * @see BasketInterface::availability()
     */
    public function canBuy(Customer $customer, bool $force = false): bool
    {
        return $this->basket->availability(new AvailabilityDto($customer, $this->getBasketDto(), $force));
    }

    public function getTotal(Customer $customer): string
    {
        $result = 0;
        $math = app(MathInterface::class);
        foreach ($this->items as $item) {
            $result = $math->add($result, $item->getAmountProduct($customer));
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

    public function getBasketDto(): BasketDto
    {
        $productDto = [];
        foreach ($this->getUniqueItems() as $product) {
            $productDto[] = new ProductDto($product, $this->getQuantity($product));
        }

        return new BasketDto($productDto, $this->getMeta());
    }

    protected function addQuantity(Product $product, int $quantity): void
    {
        $class = get_class($product);
        $uniq = $product->getUniqueId();
        $math = app(MathInterface::class);
        $this->quantity[$class][$uniq] = $math->add($this->getQuantity($product), $quantity);
    }
}
