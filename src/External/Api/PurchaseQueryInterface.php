<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface PurchaseQueryInterface
{
    public function getCustomer(): Customer;

    public function getProduct(): ProductInterface;

    public function getReceiving(): ?Wallet;

    public function includeGifts(): bool;
}
