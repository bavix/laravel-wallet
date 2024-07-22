<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\TaxServiceInterface;
use Bavix\Wallet\Services\TransferServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait CanExchange
{
    /**
     * Exchange currency from this wallet to another wallet.
     *
     * This method attempts to exchange currency from this wallet to another wallet.
     * It uses the AtomicServiceInterface to ensure atomicity and consistency of the exchange.
     * The ConsistencyServiceInterface is used to check if the exchange is possible before attempting it.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return Transfer The created transfer.
     *
     * @throws BalanceIsEmpty             if the wallet does not have enough funds to make the exchange.
     * @throws InsufficientFunds          if the wallet does not have enough funds to make the exchange.
     * @throws RecordNotFoundException    if the wallet does not exist.
     * @throws RecordsNotFoundException   if the wallet does not exist.
     * @throws TransactionFailedException if the transaction fails.
     * @throws ExceptionInterface         if an unexpected error occurs.
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        // Execute the exchange operation atomically
        // AtomicServiceInterface ensures that the exchange operation is performed as a single, indivisible action
        // to prevent race conditions and ensure consistency.
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $amount, $meta): Transfer {
            // Check if the exchange is possible before attempting it
            // ConsistencyServiceInterface checks if the exchange is possible before attempting it.
            // This helps to avoid unnecessary failures and ensures that the exchange is valid.
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            // Perform the exchange
            // forceExchange is called to perform the actual exchange of currency.
            // If the exchange is not possible, an exception is thrown.
            return $this->forceExchange($to, $amount, $meta);
        });
    }

    /**
     * Safely attempts to exchange currency from this wallet to another wallet.
     *
     * This method attempts to exchange currency from this wallet to another wallet.
     * If an error occurs during the process, null is returned.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return null|Transfer The created transfer, or null if an error occurred.
     *
     * @throws ExceptionInterface If an unexpected error occurs.
     */
    public function safeExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): ?Transfer
    {
        try {
            // Execute the exchange operation and return the created transfer.
            // If an error occurs during the process, an exception is thrown.
            return $this->exchange($to, $amount, $meta);
        } catch (ExceptionInterface $e) {
            // If an exception occurs during the exchange process, return null.
            return null;
        }
    }

    /**
     * Force exchange currency from this wallet to another wallet.
     *
     * This method will throw an exception if the exchange is not possible.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return Transfer The created transfer.
     *
     * @throws RecordNotFoundException If the wallet does not exist.
     * @throws RecordsNotFoundException If the wallet does not exist.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an unexpected error occurs.
     */
    public function forceExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        // Get the atomic service to execute the exchange operation as a single, indivisible action.
        $atomicService = app(AtomicServiceInterface::class);

        // Get the extra assembler to assemble the extra data for the transaction.
        $extraAssembler = app(ExtraDtoAssemblerInterface::class);

        // Get the prepare service to prepare the transfer operation.
        $prepareService = app(PrepareServiceInterface::class);

        // Get the math service to perform mathematical operations.
        $mathService = app(MathServiceInterface::class);

        // Get the cast service to cast the wallet to the correct type.
        $castService = app(CastServiceInterface::class);

        // Get the tax service to calculate the tax fee.
        $taxService = app(TaxServiceInterface::class);

        // Get the exchange service to convert the currency.
        $exchangeService = app(ExchangeServiceInterface::class);

        // Get the transfer lazy DTO assembler to assemble the transfer lazy DTO.
        $transferLazyDtoAssembler = app(TransferLazyDtoAssemblerInterface::class);

        // Get the transfer service to apply the transfer operation.
        $transferService = app(TransferServiceInterface::class);

        // Execute the exchange operation as a single, indivisible action.
        return $atomicService->block($this, function () use (
            $to,
            $amount,
            $meta,
            $extraAssembler,
            $prepareService,
            $mathService,
            $castService,
            $taxService,
            $exchangeService,
            $transferLazyDtoAssembler,
            $transferService
        ): Transfer {
            // Assemble the extra data for the transaction.
            $extraDto = $extraAssembler->create($meta);

            // Calculate the tax fee.
            $fee = $taxService->getFee($to, $amount);

            // Convert the currency to the target wallet currency.
            $rate = $exchangeService->convertTo(
                $castService->getWallet($this)
                    ->getCurrencyAttribute(),
                $castService->getWallet($to)
                    ->currency,
                1
            );

            // Get the withdraw option from the extra data.
            $withdrawOption = $extraDto->getWithdrawOption();

            // Prepare the withdraw operation.
            $withdrawDto = $prepareService->withdraw(
                $this,
                $mathService->add($amount, $fee),
                $withdrawOption->getMeta(),
                $withdrawOption->isConfirmed(),
                $withdrawOption->getUuid(),
            );

            // Get the deposit option from the extra data.
            $depositOption = $extraDto->getDepositOption();

            // Prepare the deposit operation.
            $depositDto = $prepareService->deposit(
                $to,
                $mathService->floor($mathService->mul($amount, $rate, 1)),
                $depositOption->getMeta(),
                $depositOption->isConfirmed(),
                $depositOption->getUuid(),
            );

            // Assemble the transfer lazy DTO.
            $transferLazyDto = $transferLazyDtoAssembler->create(
                $this,
                $to,
                0,
                $fee,
                $withdrawDto,
                $depositDto,
                Transfer::STATUS_EXCHANGE,
                $extraDto->getUuid(),
                $extraDto->getExtra()
            );

            // Apply the transfer operation.
            $transfers = $transferService->apply([$transferLazyDto]);

            // Return the created transfer.
            return current($transfers);
        });
    }
}
