<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Trait HasWallets
 * To use a trait, you must add HasWallet trait.
 *
 * @package Bavix\Wallet\Traits
 *
 * @property-read Collection|WalletModel[] $wallets
 */
trait HasWallets
{

    /**
     * The variable is used for the cache, so as not to request wallets many times.
     * WalletProxy keeps the money wallets in the memory to avoid errors when you
     * purchase/transfer, etc.
     *
     * @var array
     */
    private $_wallets = [];

    /**
     * method of obtaining all wallets
     *
     * @return MorphMany
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(config('wallet.wallet.model'), 'holder');
    }

    /**
     * Get wallet by slug
     *
     *  $user->wallet->balance // 200
     *  or short recording $user->balance; // 200
     *
     *  $defaultSlug = config('wallet.wallet.default.slug');
     *  $user->getWallet($defaultSlug)->balance; // 200
     *
     *  $user->getWallet('usd')->balance; // 50
     *  $user->getWallet('rub')->balance; // 100
     *
     * @param string $slug
     * @return WalletModel|null
     */
    public function getWallet(string $slug): ?WalletModel
    {
        if (!\array_key_exists($slug, $this->_wallets)) {
            $this->_wallets[$slug] = $this->wallets()
                ->where('slug', $slug)
                ->first();
        }

        return $this->_wallets[$slug];
    }

}
