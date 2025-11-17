<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CastServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait MorphOneWallet.
 *
 * @property WalletModel $wallet
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait MorphOneWallet
{
    /**
     * Get default Wallet. This method is used for Eager Loading.
     *
     * @return MorphOne<WalletModel, Model>
     */
    public function wallet(): MorphOne
    {
        // Get the CastService instance from the application container.
        $castService = app(CastServiceInterface::class);

        /**
         * Get the related wallet model class name from the configuration.
         * If not found, use the default wallet model class name.
         *
         * @var class-string<WalletModel>
         */
        $related = config('wallet.wallet.model', WalletModel::class);

        // Eager load the wallet for the related model.
        /** @var MorphOne<WalletModel, Model> $morphOne */
        $morphOne = $castService
            ->getHolder($this) // Get the related holder model.
            ->morphOne($related, 'holder') // Define the Eloquent relationship.
            ->withTrashed() // Include soft deleted wallets.
            ->where(
                'slug',
                (string) config('wallet.wallet.default.slug', 'default')
            ) // Filter by the default wallet slug.
            ->withDefault(static function (WalletModel $wallet, object $holder) use (
                $castService
            ) { // Define the default wallet values.
                // Get the related model.
                $model = $castService->getModel($holder);

                // Get the dynamic default slug from the related model, if available.
                // Otherwise, use the default slug from the configuration.
                /** @var string $slug */
                $slug = method_exists($model, 'getDynamicDefaultSlug')
                    ? $model->getDynamicDefaultSlug()
                    : config('wallet.wallet.default.slug', 'default');

                // Fill the default wallet attributes.
                /** @var array<string, mixed> $creating */
                $creating = config('wallet.wallet.creating', []);
                /** @var array<string, mixed> $defaultMeta */
                $defaultMeta = config('wallet.wallet.default.meta', []);
                /** @var array<string, mixed> $attributes */
                $attributes = array_merge($creating, [
                    'name' => (string) config('wallet.wallet.default.name', 'Default Wallet'), // Default wallet name.
                    'slug' => $slug, // Default wallet slug.
                    'meta' => $defaultMeta, // Default wallet metadata.
                    'balance' => 0, // Default wallet balance.
                ]);
                $wallet->forceFill($attributes);

                if ($model->exists) {
                    // Set the related model on the wallet.
                    $wallet->setRelation('holder', $model->withoutRelations());
                }
            });

        return $morphOne;
    }

    /**
     * Get the wallet attribute.
     *
     * @return WalletModel|null The wallet model associated with the related model.
     */
    public function getWalletAttribute(): ?WalletModel
    {
        /**
         * Retrieve the wallet model associated with the related model.
         *
         * @var WalletModel|null
         */
        $wallet = $this->getRelationValue('wallet');

        // If the wallet model exists and the 'holder' relationship is not loaded,
        // associate the related model with the wallet.
        if ($wallet && ! $wallet->relationLoaded('holder')) {
            // Get the related holder model.
            $holder = app(CastServiceInterface::class)->getHolder($this);

            // Associate the related model with the wallet.
            $wallet->setRelation('holder', $holder->withoutRelations());
        }

        return $wallet;
    }
}
