<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * If the product is always in stock, then the ProductInterface must be used. If the product may not be available, then
 * there is a need to use the ProductLimitedInterface.
 *
 * @deprecated The class is deprecated. Will be removed in the future.
 * @see ProductInterface
 * @see ProductLimitedInterface
 */
interface Product extends ProductLimitedInterface
{
}
