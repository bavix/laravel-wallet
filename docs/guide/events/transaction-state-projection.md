# Transaction State Projection

Issue [#1015](https://github.com/bavix/laravel-wallet/issues/1015) needs per-transaction state
(`balance_before`, `balance_after`, `state_hash`) in one atomic batch.

Package gives extension points for this, not hardcoded business projection.

State-aware context is opt-in.
If you do nothing, package inserts regular `TransactionDto` and there is no extra state calculation.

To enable state-aware flow, return `StateAwareTransactionDto` from custom
`TransactionDtoAssemblerInterface`, then map fields in custom `TransactionDtoTransformerInterface`.

## 1) Add columns

Add custom columns to `transactions` in your app migration.

## 2) Register custom assembler + transformer

```php
'assemblers' => [
    'state_aware_transaction' => \App\Wallet\TransactionStateAwareDtoAssembler::class,
],

'transformers' => [
    'transaction' => \App\Wallet\TransactionStateDtoTransformer::class,
],
```

## 3) Implement state-aware DTO assembler

```php
use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionStateAwareDtoAssembler implements TransactionDtoAssemblerInterface
{
    public function __construct(
        private TransactionDtoAssembler $base,
    ) {
    }

    public function create(
        Model $payable,
        int $walletId,
        TransactionType $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta,
        ?string $uuid
    ): TransactionDtoInterface {
        $dto = $this->base->create($payable, $walletId, $type, $amount, $confirmed, $meta, $uuid);

        // values are placeholders; package fills real before/after in transaction apply flow
        return new StateAwareTransactionDto($dto, '0', '0');
    }
}
```

## 4) Implement transformer

```php
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final readonly class TransactionStateDtoTransformer implements TransactionDtoTransformerInterface
{
    public function __construct(
        private MathServiceInterface $mathService,
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $result = [
            'uuid' => $dto->getUuid(),
            'payable_type' => $dto->getPayableType(),
            'payable_id' => $dto->getPayableId(),
            'wallet_id' => $dto->getWalletId(),
            'type' => $dto->getType()->value,
            'amount' => $dto->getAmount(),
            'confirmed' => $dto->isConfirmed(),
            'meta' => $dto->getMeta(),
            'created_at' => $dto->getCreatedAt(),
            'updated_at' => $dto->getUpdatedAt(),
        ];

        if (! $dto instanceof StateAwareTransactionDtoInterface) {
            return $result;
        }

        $result['balance_before'] = $dto->getBalanceBefore();
        $result['balance_after'] = $dto->getBalanceAfter();
        $result['state_hash'] = hash(
            'sha256',
            $dto->getUuid()
            .':'.$this->mathService->round($dto->getAmount())
            .':'.$dto->getBalanceBefore()
            .':'.$dto->getBalanceAfter()
        );

        return $result;
    }
}
```

`balance_before` and `balance_after` are sequentially correct inside single atomic operation,
including mixed wallets in same batch.

See wallet-side columns guide: [Wallet State Projection](/guide/events/wallet-state-projection).
