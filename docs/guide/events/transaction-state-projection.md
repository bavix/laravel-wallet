# Transaction State Projection

Issue [#1015](https://github.com/bavix/laravel-wallet/issues/1015) needs per-transaction state
(`balance_before`, `balance_after`, `state_hash`) in one atomic batch.

Package gives extension points for this, not hardcoded business projection.

Recommended approach: custom `TransactionDtoTransformerInterface`.

Core calculates `balance_before`/`balance_after` context for each DTO before insert.
Your transformer maps this context to custom columns and can compute `state_hash` by your rule.

## 1) Add columns

Add custom columns to `transactions` in your app migration.

## 2) Register custom transformer

```php
'transformers' => [
    'transaction' => \App\Wallet\TransactionStateDtoTransformer::class,
],
```

## 3) Implement transformer

```php
use Bavix\Wallet\Internal\Dto\StateAwareTransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;

final class TransactionStateDtoTransformer extends TransactionDtoTransformer
{
    public function extract(TransactionDtoInterface $dto): array
    {
        $result = parent::extract($dto);

        if (! $dto instanceof StateAwareTransactionDtoInterface) {
            return $result;
        }

        $result['balance_before'] = $dto->getBalanceBefore();
        $result['balance_after'] = $dto->getBalanceAfter();
        $result['state_hash'] = $dto->getStateHash();

        return $result;
    }
}
```

`balance_before` and `balance_after` are sequentially correct inside single atomic operation,
including mixed wallets in same batch.

See wallet-side columns guide: [Wallet State Projection](/guide/events/wallet-state-projection).
