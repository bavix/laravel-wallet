<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\WalletServiceInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use function array_key_exists;
use function config;

/**
 * Trait HasWallets To use a trait, you must add HasWallet trait.
 *
 * @property Collection<WalletModel> $wallets
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasWallets
{
    /**
     * The variable is used for the cache, so as not to request wallets many times. WalletProxy keeps the money wallets
     * in the memory to avoid errors when you purchase/transfer, etc.
     *
     * @var WalletModel[]
     */
    private array $_wallets = [];

    /**
     * Get wallet by slug.
     *
     * $user->wallet->balance // 200 or short recording $user->balance; // 200
     *
     * $defaultSlug = config('wallet.wallet.default.slug'); $user->getWallet($defaultSlug)->balance; // 200
     *
     * $user->getWallet('usd')->balance; // 50 $user->getWallet('rub')->balance; // 100
     */
    public function getWallet(string $slug): ?WalletModel
    {
        try {
            return $this->getWalletOrFail($slug);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * Get wallet by slug.
     *
     * $user->wallet->balance // 200 or short recording $user->balance; // 200
     *
     * $defaultSlug = config('wallet.wallet.default.slug'); $user->getWallet($defaultSlug)->balance; // 200
     *
     * $user->getWallet('usd')->balance; // 50 $user->getWallet('rub')->balance; // 100
     *
     * @throws ModelNotFoundException
     */
    public function getWalletOrFail(string $slug): WalletModel
    {
        if ($this->_wallets === [] && $this->relationLoaded('wallets')) {
            /** @var Collection<WalletModel> $wallets */
            $wallets = $this->getRelation('wallets');
            foreach ($wallets as $wallet) {
                $wallet->setRelation('holder', $this->withoutRelations());
                $this->_wallets[$wallet->slug] = $wallet;
            }
        }

        if (! array_key_exists($slug, $this->_wallets)) {
            $wallet = app(WalletServiceInterface::class)->getBySlug($this, $slug);
            $wallet->setRelation('holder', $this->withoutRelations());

            $this->_wallets[$slug] = $wallet;
        }

        return $this->_wallets[$slug];
    }

    /**
     * method of obtaining all wallets.
     *
     * @return MorphMany<WalletModel>
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(config('wallet.wallet.model', WalletModel::class), 'holder');
    }

    /**
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<mixed>|null,
     *     decimal_places?: positive-int,
     * } $data
     */
    public function createWallet(array $data): WalletModel
    {
        $wallet = app(WalletServiceInterface::class)->create($this, $data);
        $this->_wallets[$wallet->slug] = $wallet;
        $wallet->setRelation('holder', $this->withoutRelations());

        return $wallet;
    }

    /**
     * The method checks the existence of the wallet.
     */
    public function hasWallet(string $slug): bool
    {
        return (bool) $this->getWallet($slug);
    }
}
