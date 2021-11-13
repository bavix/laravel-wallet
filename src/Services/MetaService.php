<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\ProductInterface;
use Bavix\Wallet\Internal\CartInterface;

/** @deprecated */
final class MetaService
{
    public function getMeta(CartInterface $cart, ProductInterface $product): ?array
    {
        $metaCart = $cart->getBasketDto()->meta();
        $metaProduct = $product->getMetaProduct();

        if ($metaProduct !== null) {
            return array_merge($metaCart, $metaProduct);
        }

        if (count($metaCart) > 0) {
            return $metaCart;
        }

        return null;
    }
}
