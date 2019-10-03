<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait MorphOneWallet
 * @package Bavix\Wallet\Traits
 * @property-read WalletModel $wallet
 */
trait MorphOneWallet
{

    /**
     * Get default Wallet
     * this method is used for Eager Loading
     *
     * @return MorphOne|WalletModel
     */
    public function wallet(): MorphOne
    {
        return ($this instanceof WalletModel ? $this->holder : $this)
            ->morphOne(config('wallet.wallet.model', WalletModel::class), 'holder')
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->withDefault([
                'name' => config('wallet.wallet.default.name', 'Default Wallet'),
                'slug' => config('wallet.wallet.default.slug', 'default'),
                'balance' => 0,
            ]);
    }

}
