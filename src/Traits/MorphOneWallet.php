<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Contracts\CastInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait MorphOneWallet.
 *
 * @property WalletModel $wallet
 */
trait MorphOneWallet
{
    /**
     * Get default Wallet
     * this method is used for Eager Loading.
     */
    public function wallet(): MorphOne
    {
        /** @var Wallet $this */
        return app(CastInterface::class)
            ->getHolderModel($this)
            ->morphOne(config('wallet.wallet.model', WalletModel::class), 'holder')
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->withDefault(array_merge(config('wallet.wallet.creating', []), [
                'name' => config('wallet.wallet.default.name', 'Default Wallet'),
                'slug' => config('wallet.wallet.default.slug', 'default'),
                'meta' => config('wallet.wallet.default.meta', []),
                'balance' => 0,
            ]))
        ;
    }
}
