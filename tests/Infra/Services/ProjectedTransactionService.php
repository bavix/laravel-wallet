<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Services;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\AssistantServiceInterface;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Test\Infra\Dto\ProjectedTransactionDto;

final readonly class ProjectedTransactionService implements TransactionServiceInterface
{
    public function __construct(
        private TransactionCreatedEventAssemblerInterface $transactionCreatedEventAssembler,
        private DispatcherServiceInterface $dispatcherService,
        private AssistantServiceInterface $assistantService,
        private RegulatorServiceInterface $regulatorService,
        private PrepareServiceInterface $prepareService,
        private CastServiceInterface $castService,
        private AtmServiceInterface $atmService,
        private MathServiceInterface $mathService,
    ) {
    }

    /**
     * @throws RecordNotFoundException
     */
    public function makeOne(
        Wallet $wallet,
        TransactionType $type,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): Transaction {
        $dto = $type === TransactionType::Deposit
            ? $this->prepareService->deposit($wallet, (string) $amount, $meta, $confirmed)
            : $this->prepareService->withdraw($wallet, (string) $amount, $meta, $confirmed);

        $transactions = $this->apply([
            $dto->getWalletId() => $wallet,
        ], [$dto]);

        return current($transactions);
    }

    /**
     * @param non-empty-array<int, Wallet> $wallets
     * @param non-empty-array<int, TransactionDtoInterface> $objects
     * @return non-empty-array<string, Transaction>
     *
     * @throws RecordNotFoundException
     */
    public function apply(array $wallets, array $objects): array
    {
        $totals = $this->assistantService->getSums($objects);

        $currentBalances = [];
        foreach (array_keys($totals) as $walletId) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet instanceof Wallet);

            $object = $this->castService->getWallet($wallet);
            assert($object->getKey() === $walletId);

            $currentBalances[$walletId] = $this->regulatorService->amount($object);
        }

        $projectedDtos = [];
        foreach ($objects as $dto) {
            $walletId = $dto->getWalletId();
            if (! array_key_exists($walletId, $currentBalances)) {
                $wallet = $wallets[$walletId] ?? null;
                assert($wallet instanceof Wallet);

                $object = $this->castService->getWallet($wallet);
                assert($object->getKey() === $walletId);

                $currentBalances[$walletId] = $this->regulatorService->amount($object);
            }

            $nextBalance = $this->mathService->round(
                $this->mathService->add($currentBalances[$walletId], $dto->isConfirmed() ? $dto->getAmount() : '0')
            );
            $transactionAmount = $this->mathService->round($dto->getAmount());

            $meta = $dto->getMeta() ?? [];
            $meta['final_balance'] = $nextBalance;
            $meta['checksum'] = hash('sha256', $dto->getUuid().':'.$transactionAmount.':'.$nextBalance);

            $projectedDtos[] = new ProjectedTransactionDto($dto, $meta);
            $currentBalances[$walletId] = $nextBalance;
        }

        $transactions = $this->atmService->makeTransactions($projectedDtos);

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet instanceof Wallet);
            assert($total !== '');

            $object = $this->castService->getWallet($wallet);
            assert($object->getKey() === $walletId);

            $this->regulatorService->increase($object, $total);
        }

        foreach ($transactions as $transaction) {
            $this->dispatcherService->dispatch($this->transactionCreatedEventAssembler->create($transaction));
        }

        return $transactions;
    }
}
