<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @api
 */
interface CastServiceInterface
{
    /**
     * Retrieve a wallet from an object that implements the Wallet interface.
     *
     * @param Wallet $object The object that implements the Wallet interface.
     *                       This can be either a `Model` instance or an instance of a class that implements the
     *                       `Wallet` interface.
     * @param bool $save Flag indicating whether to save the wallet.
     *                    If set to `true`, the wallet will be saved if it does not exist yet.
     *                    If set to `false`, the wallet will be retrieved from the database if it exists,
     *                    otherwise a new wallet will be created.
     * @return WalletModel The retrieved wallet model.
     *
     * @throws \Bavix\Wallet\Internal\Exceptions\ModelNotFoundException If the wallet does not exist and `$save` is set to `false`.
     *
     * @see Wallet
     * @see WalletModel
     */
    public function getWallet(Wallet $object, bool $save = true): WalletModel;

    /**
     * Get the holder associated with the object.
     *
     * This method retrieves the holder model associated with the provided object. The object can be an instance of
     * the `Model` class or an instance of the `Wallet` interface. If the object is an instance of the `Wallet` interface,
     * the method directly retrieves the holder model from the `Wallet` instance. If the object is an instance of the
     * `Model` class, the method first checks if the object has a `getAttribute` method. If it does, the method attempts
     * to retrieve an attribute named 'wallet'. If the attribute exists and is an instance of `WalletModel`, the method
     * returns the `WalletModel` instance.
     *
     * @param Model|Wallet $object The object to retrieve the holder from.
     * @return Model The holder model.
     */
    public function getHolder(Model|Wallet $object): Model;

    /**
     * Retrieve the model from a given object.
     *
     * This method is responsible for extracting a model instance from the provided object, ensuring it adheres
     * to the `Model` interface. It first checks if the object itself is an instance of the `Model` class. If
     * it is, it returns the object immediately. If not, it further checks if the object has a `getAttribute`
     * method. If it does, it attempts to retrieve an attribute named 'wallet'. This attribute is then validated
     * to be an instance of `WalletModel` class before returning. If these conditions are not met, an assertion
     * error is triggered.
     *
     * @param object $object The object to extract the model from. Must either be an instance of `Model` or possess
     *                       a 'wallet' attribute of type `WalletModel`.
     * @return Model The model extracted from the provided object, ensuring it is of type `Model`.
     *
     * @throws \AssertionError If the 'wallet' attribute does not exist or is not an instance of the `WalletModel` class.
     */
    public function getModel(object $object): Model;
}
