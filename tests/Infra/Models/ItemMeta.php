<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

final class ItemMeta extends Model implements ProductLimitedInterface
{
    use HasWallet;

    protected $fillable = ['name', 'quantity', 'price'];

    public function getTable(): string
    {
        return 'items';
    }

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        $result = $this->quantity >= $quantity;

        if ($force) {
            return $result;
        }

        return $result && ! $customer->paid($this);
    }

    public function getAmountProduct(Customer $customer): int|string
    {
        /** @var Wallet $wallet */
        $wallet = app(CastService::class)->getWallet($customer);

        return $this->price + $wallet->holder_id;
    }

    /**
     * @return array{name: string, price: int}
     */
    public function getMetaProduct(): ?array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
        ];
    }
}
