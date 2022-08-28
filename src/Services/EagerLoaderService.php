<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Repository\WalletRepositoryInterface;

/**
 * @internal
 */
final class EagerLoaderService implements EagerLoaderServiceInterface
{
    public function __construct(
        private CastServiceInterface $castService,
        private WalletRepositoryInterface $walletRepository
    ) {
    }

    public function loadWalletsByBasket(BasketDtoInterface $basketDto): void
    {
        $products = [];
        /** @var array<array-key, array<array-key, int|string>> $productGroupIds */
        $productGroupIds = [];
        foreach ($basketDto->items() as $index => $item) {
            $model = $this->castService->getModel($item->getProduct());
            if (! $model->relationLoaded('wallet')) {
                $products[$index] = $item->getProduct();
                $productGroupIds[$model->getMorphClass()][$index] = $model->getKey();
            }
        }

        foreach ($productGroupIds as $holderType => $holderIds) {
            $allWallets = $this->walletRepository->findDefaultAll($holderType, array_unique($holderIds));
            $wallets = [];
            foreach ($allWallets as $wallet) {
                $wallets[$wallet->holder_id] = $wallet;
            }

            foreach ($holderIds as $index => $holderId) {
                $wallet = $wallets[$holderId] ?? null;
                if ($wallet !== null) {
                    $model = $this->castService->getModel($products[$index]);
                    $model->setRelation('wallet', $wallet);
                }
            }
        }
    }
}
