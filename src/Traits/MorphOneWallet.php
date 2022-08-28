<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CastServiceInterface;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait MorphOneWallet.
 *
 * @property WalletModel $wallet
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait MorphOneWallet
{
    /**
     * Get default Wallet this method is used for Eager Loading.
     */
    public function wallet(): MorphOne
    {
        $castService = app(CastServiceInterface::class);

        return $castService
            ->getHolder($this)
            ->morphOne(config('wallet.wallet.model', WalletModel::class), 'holder')
            ->where('slug', config('wallet.wallet.default.slug', 'default'))
            ->withDefault(static function (WalletModel $wallet, object $holder) use ($castService) {
                $model = $castService->getModel($holder);
                $wallet->forceFill(array_merge(config('wallet.wallet.creating', []), [
                    'name' => config('wallet.wallet.default.name', 'Default Wallet'),
                    'slug' => config('wallet.wallet.default.slug', 'default'),
                    'meta' => config('wallet.wallet.default.meta', []),
                    'balance' => 0,
                ]));

                if ($model->exists) {
                    $wallet->setRelation('holder', $model->withoutRelations());
                }
            })
        ;
    }

    public function getWalletAttribute(): ?WalletModel
    {
        /** @var WalletModel $wallet */
        $wallet = $this->getRelationValue('wallet');

        if (! $wallet->relationLoaded('holder')) {
            $holder = app(CastServiceInterface::class)->getHolder($this);
            $wallet->setRelation('holder', $holder->withoutRelations());
        }

        return $wallet;
    }
}
