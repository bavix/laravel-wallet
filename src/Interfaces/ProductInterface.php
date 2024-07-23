<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductInterface extends Wallet
{
    /**
     * Get the amount of the product that can be purchased by the given customer.
     *
     * This method is used to determine the amount of the product that can be purchased
     * by the given customer. The amount can be an integer or a string.
     *
     * @param Customer $customer The customer to get the amount for.
     * @return int|non-empty-string The amount of the product that can be purchased.
     */
    public function getAmountProduct(Customer $customer): int|string;

    /**
     * Get the meta data for the product.
     *
     * The meta data is an array of key-value pairs that provides additional
     * information about the product. It can be used to include a title,
     * description, or any other relevant details.
     *
     * @return array<mixed>|null The meta data for the product, or null if there is no meta data.
     *
     * @example
     * The return value should be an array with key-value pairs, for example:
     *
     *     return [
     *         'title' => 'Product Title',
     *         'description' => 'Product Description',
     *         'images' => [
     *             'https://example.com/image1.jpg',
     *             'https://example.com/image2.jpg',
     *         ],
     *     ];
     */
    public function getMetaProduct(): ?array;
}
