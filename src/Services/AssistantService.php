<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;

/**
 * @internal
 */
final class AssistantService implements AssistantServiceInterface
{
    public function __construct(
        private CastServiceInterface $castService,
        private MathServiceInterface $mathService
    ) {
    }

    /**
     * @param non-empty-array<Wallet> $objects
     *
     * @return non-empty-array<int, Wallet>
     */
    public function getWallets(array $objects): array
    {
        $wallets = [];
        foreach ($objects as $object) {
            $wallet = $this->castService->getWallet($object);
            $wallets[$wallet->getKey()] = $wallet;
        }

        return $wallets;
    }

    /**
     * @param non-empty-array<array-key, TransactionDtoInterface|TransferDtoInterface> $objects
     *
     * @return non-empty-array<array-key, string>
     */
    public function getUuids(array $objects): array
    {
        return array_map(static fn ($object): string => $object->getUuid(), $objects);
    }

    /**
     * @param non-empty-array<array-key, TransactionDtoInterface> $transactions
     *
     * @return array<int, string>
     */
    public function getSums(array $transactions): array
    {
        $amounts = [];
        foreach ($transactions as $transaction) {
            if ($transaction->isConfirmed()) {
                $amounts[$transaction->getWalletId()] = $this->mathService->add(
                    $amounts[$transaction->getWalletId()] ?? 0,
                    $transaction->getAmount()
                );
            }
        }

        return array_filter($amounts, fn (string $amount): bool => $this->mathService->compare($amount, 0) !== 0);
    }

    public function getMeta(BasketDtoInterface $basketDto, ProductInterface $product): ?array
    {
        $metaBasket = $basketDto->meta();
        $metaProduct = $product->getMetaProduct();

        if ($metaProduct !== null) {
            return array_merge($metaBasket, $metaProduct);
        }

        if ($metaBasket !== []) {
            return $metaBasket;
        }

        return null;
    }
}
