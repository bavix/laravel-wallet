<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Objects\Cart;

class CartService
{

    /**
     * @param Product[] $products
     * @return Cart
     */
    public function create(array $products = []): Cart
    {
        $cart = new Cart();
        foreach ($products as $product) {
            $cart->addItem($product);
        }
        return $cart;
    }

}
