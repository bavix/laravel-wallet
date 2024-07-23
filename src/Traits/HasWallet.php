<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Services\TransferServiceInterface;
use function config;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;

/**
 * Trait HasWallet.
 *
 * @property WalletModel $wallet
 * @property string $balance
 * @property int $balanceInt
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Wallet
 */
trait HasWallet
{
    use MorphOneWallet;

    /**
     * Deposit funds into the wallet.
     *
     * This method executes the deposit transaction within an atomic block to ensure data consistency.
     *
     * @param int|string $amount The amount to deposit.
     * @param array<mixed>|null $meta Additional metadata for the transaction. This can be used to store
     * information about the type of deposit, the source of the funds, or any other relevant details.
     * @param bool $confirmed Whether the transaction is confirmed. This can be used to indicate whether the
     * transaction has been verified and is considered final. Defaults to true.
     * @return Transaction The transaction object representing the deposit.
     *
     * @throws AmountInvalid If the amount is invalid (e.g. negative, not a number, too large).
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails for any reason (e.g. network issues, insufficient funds).
     * @throws ExceptionInterface If an exception occurs during the transaction process.
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        // Execute the deposit transaction within an atomic block to ensure data consistency.
        return app(AtomicServiceInterface::class)->block(
            $this,
            // Create a new deposit transaction.
            fn () => app(TransactionServiceInterface::class)
                ->makeOne($this, Transaction::TYPE_DEPOSIT, $amount, $meta, $confirmed)
        );
    }

    /**
     * Magic Laravel framework method that makes it possible to call property balance.
     *
     * This method is called by Laravel's magic getter when the `balance` property is accessed.
     * It returns the current balance of the wallet as a string.
     *
     * @return non-empty-string The current balance of the wallet as a string.
     *
     * @throws \Bavix\Wallet\Internal\Exceptions\ModelNotFoundException If the wallet does not exist and `$save` is set to `false`.
     *
     * @see Wallet
     * @see WalletModel
     */
    public function getBalanceAttribute(): string
    {
        // Get the wallet object from the model.
        // This method uses the CastServiceInterface to retrieve the wallet object from the model.
        // The second argument, `$save = false`, prevents the service from saving the wallet if it does not exist.
        // This is useful to avoid unnecessary database queries when retrieving the balance.
        $wallet = app(CastServiceInterface::class)->getWallet($this, false);

        // Get the current balance of the wallet using the Regulator service.
        // This method uses the RegulatorServiceInterface to retrieve the current balance of the wallet.
        // The Regulator service is responsible for calculating the balance of the wallet based on the transactions.
        // The balance is always returned as a string to preserve the accuracy of the decimal value.
        $balance = app(RegulatorServiceInterface::class)->amount($wallet);

        // Return the balance as a string.
        return $balance;
    }

    /**
     * Returns the balance of the wallet as an integer.
     *
     * This method is called by Laravel's magic getter when the `balanceInt` property is accessed.
     * It retrieves the current balance of the wallet as a string using the `getBalanceAttribute` method.
     * The decimal value is preserved by converting the string to an integer.
     *
     * @return int The current balance of the wallet as an integer.
     *
     * @throws \Bavix\Wallet\Internal\Exceptions\ModelNotFoundException If the wallet does not exist and `$save` is set to `false`.
     *
     * @see Wallet
     * @see WalletModel
     * @see HasWallet::getBalanceAttribute
     */
    public function getBalanceIntAttribute(): int
    {
        // Get the current balance of the wallet as a string.
        // This is done using the `getBalanceAttribute` method.
        $balanceString = $this->getBalanceAttribute();

        // Convert the balance string to an integer.
        // This ensures that the decimal value is preserved while converting it to an integer.
        $balanceInt = (int) $balanceString;

        // Return the balance as an integer.
        return $balanceInt;
    }

    /**
     * Returns all transactions related to the wallet.
     *
     * This method retrieves all transactions associated with the wallet.
     * It uses the `getWallet` method of the `CastServiceInterface` to retrieve the wallet instance.
     * The `false` parameter indicates that the wallet should not be saved if it does not exist.
     * The method then uses the `hasMany` method on the wallet instance to retrieve all transactions related to the wallet.
     * The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
     * The relationship is defined using the `wallet_id` foreign key.
     *
     * @return HasMany<Transaction> Returns a `HasMany` relationship of transactions related to the wallet.
     */
    public function walletTransactions(): HasMany
    {
        // Retrieve the wallet instance using the `getWallet` method of the `CastServiceInterface`.
        // The `false` parameter indicates that the wallet should not be saved if it does not exist.
        $wallet = app(CastServiceInterface::class)->getWallet($this, false);

        // Retrieve all transactions related to the wallet using the `hasMany` method on the wallet instance.
        // The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
        // The relationship is defined using the `wallet_id` foreign key.
        $transactions = $wallet->hasMany(config('wallet.transaction.model', Transaction::class), 'wallet_id');

        return $transactions;
    }

    /**
     * Retrieves all user actions related to the wallet.
     *
     * This method returns a `MorphMany` relationship object that represents all transactions and transfers
     * associated with the wallet. It fetches the wallet instance using the `getWallet` method of the
     * `CastServiceInterface` and defines the relationship using the `morphMany` method on the wallet instance.
     * The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
     * The relationship is defined using the `payable` foreign key.
     *
     * @return MorphMany<Transaction> The `MorphMany` relationship object representing all user actions on the wallet.
     */
    public function transactions(): MorphMany
    {
        // Fetch the wallet instance using the `getWallet` method of the `CastServiceInterface`.
        // The `getWallet` method is responsible for retrieving the wallet instance associated with the current model.
        $wallet = app(CastServiceInterface::class)->getHolder($this);

        // Define the relationship between the wallet and the transactions using the `morphMany` method.
        // The `morphMany` method is used to define a polymorphic one-to-many relationship.
        // In this case, it represents the relationship between the wallet and the transactions.
        // The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
        // The relationship is defined using the `payable` foreign key.
        // The `payable` foreign key is used to associate the transactions with the wallet.
        return $wallet->morphMany(
            config('wallet.transaction.model', Transaction::class),
            'payable' // The name of the polymorphic relation column.
        );
    }

    /**
     * Safely transfers funds from this wallet to another.
     *
     * This method attempts to transfer funds from this wallet to another wallet.
     * If an error occurs during the process, null is returned.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return null|Transfer The created transaction, or null if an error occurred.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function safeTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer {
        // Attempt to transfer the funds from this wallet to the specified wallet.
        try {
            // Use the `transfer` method to transfer the funds.
            // The `transfer` method is responsible for performing the actual transfer of funds.
            // If an error occurs during the process, an exception is thrown.
            return $this->transfer($wallet, $amount, $meta);
        } catch (ExceptionInterface $e) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host.
     *
     * This method attempts to transfer funds from the host wallet to another wallet.
     * It uses the `AtomicServiceInterface` to ensure atomicity and consistency of the transfer.
     * The `ConsistencyServiceInterface` is used to check if the transfer is possible before attempting it.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return Transfer The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     *
     * @see AtomicServiceInterface
     * @see ConsistencyServiceInterface
     * @see TransactionFailedException
     * @see AmountInvalid
     * @see BalanceIsEmpty
     * @see InsufficientFunds
     * @see RecordsNotFoundException
     */
    public function transfer(Wallet $wallet, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        // Wrap the transfer in an atomic block to ensure consistency and prevent race conditions.
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            /** @var Wallet $this */
            // Check if the transfer is possible before attempting it.
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            // Perform the transfer.
            return $this->forceTransfer($wallet, $amount, $meta);
        });
    }

    /**
     * Withdraw funds from the system.
     *
     * This method wraps the withdrawal in an atomic block to ensure atomicity and consistency of the withdrawal.
     * It checks if the withdrawal is possible before attempting it.
     *
     * @param int|string $amount The amount to withdraw.
     * @param array<mixed>|null $meta Additional information for the transaction.
     * @param bool $confirmed Whether the withdrawal is confirmed.
     * @return Transaction The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     *
     * @see AtomicServiceInterface
     * @see ConsistencyServiceInterface
     * @see TransactionFailedException
     * @see AmountInvalid
     * @see BalanceIsEmpty
     * @see InsufficientFunds
     * @see RecordsNotFoundException
     */
    public function withdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        // Wrap the withdrawal in an atomic block to ensure consistency and prevent race conditions.
        return app(AtomicServiceInterface::class)->block($this, function () use (
            $amount,
            $meta,
            $confirmed
        ): Transaction {
            /** @var Wallet $this */
            // Check if the withdrawal is possible before attempting it.
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            // Perform the withdrawal.
            return $this->forceWithdraw($amount, $meta, $confirmed);
        });
    }

    /**
     * Checks if the user can withdraw funds based on the provided amount.
     *
     * This method retrieves the math service instance and calculates the total balance of the wallet.
     * It then checks if the withdrawal is possible using the consistency service.
     *
     * @param int|string $amount The amount to be withdrawn.
     * @param bool $allowZero Flag to allow zero balance for withdrawal. Defaults to false.
     * @return bool Returns true if the withdrawal is possible; otherwise, false.
     */
    public function canWithdraw(int|string $amount, bool $allowZero = false): bool
    {
        // Get the math service instance.
        $mathService = app(MathServiceInterface::class);

        // Get the wallet and calculate the total balance.
        $wallet = app(CastServiceInterface::class)->getWallet($this);
        $balance = $mathService->add($this->getBalanceAttribute(), $wallet->getCreditAttribute());

        // Check if the withdrawal is possible.
        return app(ConsistencyServiceInterface::class)
            ->canWithdraw($balance, $amount, $allowZero);
    }

    /**
     * Forced to withdraw funds from the system.
     *
     * This method creates a new withdrawal transaction and returns it. It wraps the transaction creation
     * in an atomic block to ensure atomicity and consistency.
     *
     * @param int|string $amount The amount to withdraw.
     * @param array<mixed>|null $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed. Defaults to true.
     * @return Transaction The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceWithdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        // Wrap the transaction creation in an atomic block to ensure atomicity and consistency.
        // The atomic block ensures that the creation of the transaction is atomic,
        // meaning that either the entire transaction is created or none of it is.
        return app(AtomicServiceInterface::class)->block(
        // The wallet instance
            $this,
            function () use ($amount, $meta, $confirmed): Transaction {
                // Create a new withdrawal transaction.
                return app(TransactionServiceInterface::class)->makeOne(
                // The wallet instance
                    $this,
                    // The transaction type
                    Transaction::TYPE_WITHDRAW,
                    // The amount to withdraw
                    $amount,
                    // Additional information for the transaction
                    $meta,
                    // Whether the transaction is confirmed
                    $confirmed
                );
            }
        );
    }

    /**
     * Forces a transfer of funds from this wallet to another, bypassing certain safety checks.
     *
     * This method is intended for use in scenarios where a transfer must be completed regardless of
     * the usual validation checks (e.g., sufficient funds, wallet status). It is critical to use this
     * method with caution as it can result in negative balances or other unintended consequences.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return Transfer The created transfer.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer {
        // Wrap the transfer creation in an atomic block to ensure atomicity and consistency.
        // The atomic block ensures that the creation of the transfer is atomic,
        // meaning that either the entire transfer is created or none of it is.
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            // Create a new transfer transaction.
            // The transfer transaction is created using the PrepareServiceInterface.
            // The transfer status is set to Transfer::STATUS_TRANSFER.
            // The additional information for the transaction is passed as an argument.
            // The created transfer transaction is stored in the $transferLazyDto variable.
            $transferLazyDto = app(PrepareServiceInterface::class)
                ->transferLazy($this, $wallet, Transfer::STATUS_TRANSFER, $amount, $meta);

            // Apply the transfer transaction.
            // The transfer transaction is applied using the TransferServiceInterface.
            // The created transfer is returned.
            // The $transferLazyDto is passed as an array to the apply method
            // to create the transfer transaction.
            // The applied transfer transaction is stored in the $transfers variable.
            // The current transfer transaction is returned.
            $transfers = app(TransferServiceInterface::class)->apply([$transferLazyDto]);

            return current($transfers);
        });
    }

    /**
     * Retrieves all transfers related to the wallet.
     *
     * This method retrieves all transfers associated with the wallet.
     * It uses the `getWallet` method of the `CastServiceInterface` to retrieve the wallet instance.
     * The `false` parameter indicates that the wallet should not be saved if it does not exist.
     * The method then uses the `hasMany` method on the wallet instance to retrieve all transfers related to the wallet.
     * The transfer model class is retrieved from the configuration using `config('wallet.transfer.model', Transfer::class)`.
     * The relationship is defined using the `from_id` foreign key.
     *
     * @return HasMany<Transfer> The `HasMany` relationship object representing all transfers related to the wallet.
     */
    public function transfers(): HasMany
    {
        // Retrieve the wallet instance associated with the current model.
        // The `getWallet` method of the `CastServiceInterface` is used to retrieve the wallet instance.
        // The `false` parameter indicates that the wallet should not be saved if it does not exist.
        $wallet = app(CastServiceInterface::class)
            ->getWallet($this, false);

        // Retrieve all transfers associated with the wallet.
        // The `hasMany` method is used on the wallet instance to retrieve all transfers related to the wallet.
        // The transfer model class is retrieved from the configuration using `config('wallet.transfer.model', Transfer::class)`.
        // The relationship is defined using the `from_id` foreign key.
        return $wallet
            ->hasMany(
            // Retrieve the transfer model class from the configuration.
            // The default value is `Transfer::class`.
                config('wallet.transfer.model', Transfer::class),
                // Define the foreign key for the relationship.
                // The foreign key is `from_id`.
                'from_id'
            );
    }

    /**
     * Retrieves all the receiving transfers to this wallet.
     *
     * This method retrieves all receiving transfers associated with the wallet.
     * It uses the `getWallet` method of the `CastServiceInterface` to retrieve the wallet instance.
     * The `false` parameter indicates that the wallet should not be saved if it does not exist.
     * The method then uses the `hasMany` method on the wallet instance to retrieve all receiving transfers related to the wallet.
     * The transfer model class is retrieved from the configuration using `config('wallet.transfer.model', Transfer::class)`.
     * The relationship is defined using the `to_id` foreign key.
     *
     * @return HasMany<Transfer> The `HasMany` relationship object representing all receiving transfers related to the wallet.
     */
    public function receivedTransfers(): HasMany
    {
        // Retrieve the wallet instance associated with the current model.
        // The `getWallet` method of the `CastServiceInterface` is used to retrieve the wallet instance.
        // The `false` parameter indicates that the wallet should not be saved if it does not exist.
        $wallet = app(CastServiceInterface::class)
            ->getWallet($this, false);

        // Retrieve all receiving transfers associated with the wallet.
        // The `hasMany` method is used on the wallet instance to retrieve all receiving transfers related to the wallet.
        // The transfer model class is retrieved from the configuration using `config('wallet.transfer.model', Transfer::class)`.
        // The relationship is defined using the `to_id` foreign key.
        return $wallet
            ->hasMany(
            // Retrieve the transfer model class from the configuration.
            // The default value is `Transfer::class`.
                config('wallet.transfer.model', Transfer::class),
                // Define the foreign key for the relationship.
                // The foreign key is `to_id`.
                'to_id'
            );
    }
}
