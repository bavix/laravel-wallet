<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Internal\Service\ConfigServiceInterface;
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
        $configService = app(ConfigServiceInterface::class);
        $castService = app(CastServiceInterface::class);
        $related = $configService->getClass('wallet.wallet.model', WalletModel::class);
        $slug = $configService->getString('wallet.wallet.default.slug', 'default');

        return $castService
            ->getHolder($this)
            ->morphOne($related, 'holder')
            ->where('slug', $slug)
            ->withDefault(static function (WalletModel $wallet, object $holder) use ($configService, $castService) {
                $model = $castService->getModel($holder);
                $wallet->forceFill(array_merge($configService->getArray('wallet.wallet.creating'), [
                    'name' => $configService->getString('wallet.wallet.default.name', 'Default Wallet'),
                    'slug' => $configService->getString('wallet.wallet.default.slug', 'default'),
                    'meta' => $configService->getArray('wallet.wallet.default.meta'),
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
