<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;

/**
 * @internal
 */
final readonly class TransactionService implements TransactionServiceInterface
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

        $stateAwareTransactions = [];
        foreach ($objects as $dto) {
            $walletId = $dto->getWalletId();
            if (! array_key_exists($walletId, $currentBalances)) {
                $wallet = $wallets[$walletId] ?? null;
                assert($wallet instanceof Wallet);

                $object = $this->castService->getWallet($wallet);
                assert($object->getKey() === $walletId);

                $currentBalances[$walletId] = $this->regulatorService->amount($object);
            }

            $beforeBalance = $currentBalances[$walletId];
            $nextBalance = $this->mathService->round(
                $this->mathService->add($beforeBalance, $dto->isConfirmed() ? $dto->getAmount() : '0')
            );

            $uuid = $dto->getUuid();

            $stateAwareTransactions[$uuid] = new StateAwareTransactionDto($dto, $beforeBalance, $nextBalance);
            $currentBalances[$walletId] = $nextBalance;
        }

        $transactions = $this->atmService->makeTransactions(array_values($stateAwareTransactions)); // q1
        assert(count($objects) === count($transactions));

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet instanceof Wallet);

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
