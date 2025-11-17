<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function array_key_exists;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\WalletServiceInterface;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Trait HasWallets To use a trait, you must add HasWallet trait.
 *
 * @property Collection<WalletModel> $wallets
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasWallets
{
    /**
     * Cache for the wallets to avoid requesting them multiple times. WalletProxy stores the money wallets
     * in memory to avoid errors when you purchase/transfer, etc.
     *
     * @var array<string,WalletModel>
     */
    private array $_wallets = [];

    /**
     * Get wallet by slug.
     *
     * @param string $slug The slug of the wallet.
     * @return WalletModel|null The wallet with the given slug, or null if not found.
     *
     * This method is a wrapper around the getWalletOrFail method. It catches the ModelNotFoundException
     * and returns null instead of throwing it.
     */
    public function getWallet(string $slug): ?WalletModel
    {
        // Try to get the wallet with the given slug.
        try {
            return $this->getWalletOrFail($slug);
        } catch (ModelNotFoundException $exception) {
            // If the wallet is not found, return null.
            return null;
        }
    }

    /**
     * Get wallet by slug.
     *
     * This method loads wallets from the database if they are not loaded yet.
     * Then it retrieves the wallet with the given slug from the cache.
     * If the wallet is not found in the cache, it retrieves it from the database,
     * stores it in the cache, and returns it.
     *
     * @param string $slug The slug of the wallet.
     * @return WalletModel The wallet with the given slug.
     *
     * @throws ModelNotFoundException If the wallet with the given slug is not found.
     */
    public function getWalletOrFail(string $slug): WalletModel
    {
        // Check if wallets are loaded.
        // Load wallets if they are not loaded yet.
        if ($this->_wallets === [] && $this->relationLoaded('wallets')) {
            /** @var Collection<WalletModel> $wallets */
            $wallets = $this->getRelation('wallets');
            // Load the wallets into the cache.
            foreach ($wallets as $wallet) {
                $wallet->setRelation('holder', $this->withoutRelations());
                $this->_wallets[$wallet->slug] = $wallet;
            }
        }

        // Check if the wallet is not found in the cache.
        if (! array_key_exists($slug, $this->_wallets)) {
            // Retrieve the wallet from the database if it is not found in the cache.
            $wallet = app(WalletServiceInterface::class)->getBySlug($this, $slug);
            $wallet->setRelation('holder', $this->withoutRelations());

            // Store the wallet in the cache.
            $this->_wallets[$slug] = $wallet;
        }

        // Return the wallet from the cache.
        return $this->_wallets[$slug];
    }

    /**
     * Method for obtaining all wallets.
     *
     * This method returns a MorphMany relationship object. The relationship is
     * defined between the current model (the "holder") and the wallet model.
     * The wallet model is specified in the configuration file under the
     * 'wallet.model' key. If the key is not found, the default wallet model is
     * used.
     *
     * @return MorphMany<WalletModel, Model> The MorphMany relationship object.
     */
    public function wallets(): MorphMany
    {
        // Define a MorphMany relationship between the current model (the "holder")
        // and the wallet model.
        /** @var class-string<WalletModel> $model */
        $model = config('wallet.wallet.model', WalletModel::class);
        /** @var MorphMany<WalletModel, Model> $morphMany */
        $morphMany = $this
            ->morphMany(
                // Get the wallet model from the configuration.
                $model,
                // Specify the name of the polymorphic relation.
                'holder'
            );

        return $morphMany;
    }

    /**
     * Creates a new wallet for the current model.
     *
     * This method creates a new wallet with the given data and associates it
     * with the current model. The current model is referred to as the "holder"
     * of the wallet.
     *
     * The method can be used to create a new wallet with the following data:
     *
     * - name: The name of the wallet.
     * - slug: The slug of the wallet. If not specified, the slug is generated
     *         automatically.
     * - description: The description of the wallet.
     * - meta: The meta data for the wallet. The meta data is an array of
     *         key-value pairs.
     * - decimal_places: The number of decimal places for the wallet. If not
     *                   specified, the default value is 2.
     *
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<mixed>|null,
     *     decimal_places?: positive-int,
     * } $data The data for the new wallet.
     * @return WalletModel The new wallet object.
     */
    public function createWallet(array $data): WalletModel
    {
        // Create the wallet with the given data.
        $wallet = app(WalletServiceInterface::class)->create($this, $data);

        // Cache the wallet.
        $this->_wallets[$wallet->slug] = $wallet;

        // Set the relation between the wallet and the current model.
        $wallet->setRelation('holder', $this->withoutRelations());

        return $wallet;
    }

    /**
     * Checks the existence of a wallet.
     *
     * This method checks if a wallet with the given slug exists for the current model.
     *
     * @param string $slug The slug of the wallet.
     */
    public function hasWallet(string $slug): bool
    {
        // Check if the wallet exists by calling the getWallet() method.
        // The getWallet() method returns the wallet object if it exists,
        // or null if it does not exist.
        // Casting the result to a boolean converts null to false and the wallet
        // object to true.
        return (bool) $this->getWallet($slug);
    }
}
