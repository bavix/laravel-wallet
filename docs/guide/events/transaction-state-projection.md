# Transaction State Projection <VersionTag version="v12.0.0" />

Issue [#1015](https://github.com/bavix/laravel-wallet/issues/1015) needs per-transaction state (`balance_before`, `balance_after`, `state_hash`) in one atomic batch.

**Package gives extension points, not hardcoded business projection.** State-aware is opt-in.

## How it works (opt-in)

1. By default, package inserts regular `TransactionDto` — no extra state calculation.
2. If you want state tracking, create **custom Assembler** that uses `TransactionStateService`.
3. Register your custom Assembler in config.

No overhead for users who don't need state projection.

## 1) Add columns

Add custom columns to `transactions` table in your app migration:

```php
$table->string('balance_before')->nullable();
$table->string('balance_after')->nullable();
$table->string('state_hash', 64)->nullable();
```

## 2) Register custom assembler

```php
// config/wallet.php
'assemblers' => [
    'transaction' => \App\Wallet\StateAwareTransactionAssembler::class,
],
```

## 3) Implement custom assembler

Your assembler computes balance before/after and pushes to `TransactionStateService`:

```php
use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\TransactionStateService;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class StateAwareTransactionAssembler implements TransactionDtoAssemblerInterface
{
    public function __construct(
        private TransactionDtoAssembler $base,
        private TransactionStateService $stateService,  // Your service instance
        private RegulatorServiceInterface $regulator,
        private MathServiceInterface $mathService,
    ) {}

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

        $before = $this->regulator->amount($payable);
        $after = $before;

        if ($confirmed) {
            $after = $type === TransactionType::Deposit
                ? $this->mathService->add($before, $amount)
                : $this->mathService->sub($before, $amount);
        }

        // Push state to your service
        $this->stateService->push($dto->getUuid(), $walletId, [
            'balance' => $before,
        ], [
            'balance' => $after,
        ]);

        return $dto;
    }
}
```

## 4) Use transformer to persist state

```php
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\TransactionStateService;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final readonly class TransactionStateDtoTransformer implements TransactionDtoTransformerInterface
{
    public function __construct(
        private TransactionDtoTransformer $base,
        private TransactionStateService $stateService,
    ) {}

    public function extract(TransactionDtoInterface $dto): array
    {
        $result = $this->base->extract($dto);

        if (!$this->stateService->has($dto->getUuid())) {
            return $result;
        }

        $before = $this->stateService->before($dto->getUuid());
        $after = $this->stateService->after($dto->getUuid());

        $result['balance_before'] = $before['balance'];
        $result['balance_after'] = $after['balance'];
        $result['state_hash'] = hash('sha256', $dto->getUuid().':'.$dto->getAmount().':'.$before['balance'].':'.$after['balance']);

        return $result;
    }
}
```

## Key points

- No default `state_aware` config flag — opt-in only via custom Assembler
- `TransactionStateService` is your utility class, not auto-registered in core
- Compute balance before/after in your Assembler (not '0','0' placeholders)
- `balance_before` and `balance_after` are sequentially correct inside single atomic operation, including mixed wallets in same batch

See wallet-side columns: [Wallet State Projection](/guide/events/wallet-state-projection).