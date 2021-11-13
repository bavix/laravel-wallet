<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\Product;

/** @deprecated */
final class MetaServiceLegacy
{
    public function getMeta(CartInterface $cart, Product $product): ?array
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
