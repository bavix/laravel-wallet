<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;

final readonly class PurchaseQuery implements PurchaseQueryInterface
{
    private function __construct(
        private Customer $customer,
        private ProductInterface $product,
        private bool $includeGifts,
        private ?Wallet $receiving
    ) {
    }

    public static function create(
        Customer $customer,
        ProductInterface $product,
        bool $includeGifts = false,
        ?Wallet $receiving = null
    ): self {
        return new self($customer, $product, $includeGifts, $receiving);
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function getReceiving(): ?Wallet
    {
        return $this->receiving;
    }

    public function includeGifts(): bool
    {
        return $this->includeGifts;
    }
}
