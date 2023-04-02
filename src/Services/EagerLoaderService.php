<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Repository\WalletRepositoryInterface;
use Bavix\Wallet\Models\Wallet;

/**
 * @internal
 */
final class EagerLoaderService implements EagerLoaderServiceInterface
{
    public function __construct(
        private readonly CastServiceInterface $castService,
        private readonly WalletRepositoryInterface $walletRepository
    ) {
    }

    public function loadWalletsByBasket(Customer $customer, BasketDtoInterface $basketDto): void
    {
        $customerWallet = $this->castService->getWallet($customer);

        /** @var string|null $customerCurrency */
        $customerCurrency = $customerWallet->meta['currency'] ?? null;

        if ($customerCurrency === null) {
            $this->loadDefaultWallets($basketDto);
            return;
        }

        $this->loadCurrencyWallets($customerCurrency, $basketDto);
    }

    private function loadCurrencyWallets(string $currency, BasketDtoInterface $basketDto): void
    {
        // todo: needs to be implemented
    }

    private function loadDefaultWallets(BasketDtoInterface $basketDto): void
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
                if ($wallet instanceof Wallet) {
                    $model = $this->castService->getModel($products[$index]);
                    $model->setRelation('wallet', $wallet);
                }
            }
        }
    }
}
