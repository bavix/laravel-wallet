<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Contracts\ProductInterface;

/**
 * @deprecated Will be removed in version 7.1
 * @see ProductInterface
 */
interface Product extends ProductInterface, Wallet
{
}
