<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CastServiceInterface;
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
        return app(CastServiceInterface::class)
            ->getHolder($this)
            ->morphOne(config('wallet.wallet.model', WalletModel::class), 'holder')
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->withDefault(array_merge(config('wallet.wallet.creating', []), [
                'name' => config('wallet.wallet.default.name', 'Default Wallet'),
                'slug' => config('wallet.wallet.default.slug', 'default'),
                'meta' => config('wallet.wallet.default.meta', []),
                'uuid' => app(UuidFactoryServiceInterface::class)->uuid4(),
                'balance' => 0,
            ]))
        ;
    }
}
