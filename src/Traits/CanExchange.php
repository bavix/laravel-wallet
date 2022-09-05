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
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
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
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $amount, $meta): Transfer {
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceExchange($to, $amount, $meta);
        });
    }

    public function safeExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $amount, $meta): Transfer {
            $extraAssembler = app(ExtraDtoAssemblerInterface::class);
            $prepareService = app(PrepareServiceInterface::class);
            $mathService = app(MathServiceInterface::class);
            $castService = app(CastServiceInterface::class);
            $taxService = app(TaxServiceInterface::class);
            $fee = $taxService->getFee($to, $amount);
            $rate = app(ExchangeServiceInterface::class)->convertTo(
                $castService->getWallet($this)
                    ->getCurrencyAttribute(),
                $castService->getWallet($to)
                    ->currency,
                1
            );

            $extraDto = $extraAssembler->create($meta);
            $withdrawOption = $extraDto->getWithdrawOption();
            $depositOption = $extraDto->getDepositOption();
            $withdrawDto = $prepareService->withdraw(
                $this,
                $mathService->add($amount, $fee),
                $withdrawOption->getMeta(),
                $withdrawOption->isConfirmed(),
            );
            $depositDto = $prepareService->deposit(
                $to,
                $mathService->floor($mathService->mul($amount, $rate, 1)),
                $depositOption->getMeta(),
                $depositOption->isConfirmed(),
            );
            $transferLazyDto = app(TransferLazyDtoAssemblerInterface::class)->create(
                $this,
                $to,
                0,
                $fee,
                $withdrawDto,
                $depositDto,
                Transfer::STATUS_EXCHANGE,
            );

            $transfers = app(TransferServiceInterface::class)->apply([$transferLazyDto]);

            return current($transfers);
        });
    }
}
