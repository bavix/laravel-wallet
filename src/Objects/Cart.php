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
use Bavix\Wallet\Internal\Dto\ItemDto;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\PurchaseInterface;
use Bavix\Wallet\Models\Transfer;
use function count;
use Countable;
use function get_class;
use Illuminate\Database\Eloquent\Model;

class Cart implements Countable, CartInterface
{
    /**
     * @var Product[]
     */
    private array $items = [];

    /** @var array<string, int> */
    private array $quantity = [];

    private array $meta = [];

    private BasketInterface $basket;

    private MathInterface $math;

    public function __construct(
        BasketInterface $basket,
        MathInterface $math
    ) {
        $this->basket = $basket;
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
     *
     * @deprecated
     * @see PurchaseInterface::already()
     */
    public function alreadyBuy(Customer $customer, bool $gifts = false): array
    {
        return app(PurchaseInterface::class)->already($customer, $this->getBasketDto(), $gifts);
    }

    /**
     * @deprecated
     * @see BasketInterface::availability()
     *
     * @codeCoverageIgnore
     */
    public function canBuy(Customer $customer, bool $force = false): bool
    {
        return $this->basket->availability(new AvailabilityDto($customer, $this->getBasketDto(), $force));
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
        /** @var Model $product */
        $uniq = (string) (method_exists($product, 'getUniqueId')
            ? $product->getUniqueId()
            : $product->getKey());

        return (int) ($this->quantity[get_class($product).':'.$uniq] ?? 0);
    }

    public function getBasketDto(): BasketDto
    {
        $items = [];
        foreach ($this->getUniqueItems() as $product) {
            $items[] = new ItemDto($product, $this->getQuantity($product));
        }

        return new BasketDto($items, $this->getMeta());
    }

    protected function addQuantity(Product $product, int $quantity): void
    {
        /** @var Model|Product $product */
        $uniq = (string) (method_exists($product, 'getUniqueId')
            ? $product->getUniqueId()
            : $product->getKey());

        $this->quantity[get_class($product).':'.$uniq] = $this->math
            ->add($this->getQuantity($product), $quantity)
        ;
    }
}
