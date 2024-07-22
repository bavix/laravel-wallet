<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;

/**
 * @api
 */
interface AssistantServiceInterface
{
    /**
     * Returns an array of wallets from the objects.
     *
     * @param non-empty-array<Wallet> $objects An array of objects that implement the Wallet interface.
     * @return non-empty-array<int, Wallet> An array of wallets. The keys are the wallet IDs and the values are the
     *                                      wallet objects.
     *
     * @throws ModelNotFoundException If any of the objects does not have a wallet.
     */
    public function getWallets(array $objects): array;

    /**
     * Returns an array of UUIDs for the objects.
     *
     * @template T of non-empty-array<string>
     *
     * @param T $objects An array of objects that implement the TransactionDtoInterface or TransferDtoInterface
     *                   interfaces.
     * @return non-empty-array<key-of<T>, string> An array of UUIDs. The keys are the same keys of the input array.
     *                                              The values are the UUIDs extracted from the objects.
     *
     * @throws ModelNotFoundException If any of the objects does not have a UUID.
     */
    public function getUuids(array $objects): array;

    /**
     * Calculates the total amount for each wallet from an array of transactions.
     *
     * This function helps to quickly calculate the amount for each wallet. The array of transactions must contain
     * objects that implement the TransactionDtoInterface interface.
     *
     * @param non-empty-array<TransactionDtoInterface> $transactions An array of transactions.
     * @return array<int, string> An array with the total amount for each wallet.
     *                             The keys are the wallet IDs and the values are the total amounts as strings.
     *                             The amounts are formatted as strings with the decimal part rounded to the wallet's
     *                             decimal places.
     *
     * @throws ModelNotFoundException If any of the transactions does not have a wallet.
     *
     * @see TransactionDtoInterface
     */
    public function getSums(array $transactions): array;

    /**
     * Get the meta data for the cart.
     *
     * This function helps to get the meta data for the cart. The meta data is an array of key-value pairs that
     * provides additional information about the cart. It can be used to include a title, description, or any other
     * relevant details.
     *
     * @param BasketDtoInterface $basketDto The basket DTO object.
     * @param ProductInterface   $product    The product object.
     * @return array<mixed>|null The meta data for the cart, or null if there is no meta data.
     *
     * @example
     * The return value should be an array with key-value pairs, for example:
     *
     *     return [
     *         'title' => 'Cart Title',
     *         'description' => 'Cart Description',
     *         'images' => [
     *             'https://example.com/image1.jpg',
     *             'https://example.com/image2.jpg',
     *         ],
     *     ];
     *
     * @see BasketDtoInterface
     * @see ProductInterface
     */
    public function getMeta(BasketDtoInterface $basketDto, ProductInterface $product): ?array;
}
