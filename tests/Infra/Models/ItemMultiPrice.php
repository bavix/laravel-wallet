<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Test\Infra\Exceptions\PriceNotSetException;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 * @property array<string, int> $prices
 *
 * @method int getKey()
 */
final class ItemMultiPrice extends Model implements ProductLimitedInterface
{
    use HasWallet;

    /**
     * @var array<int,string>
     */
    protected $fillable = ['name', 'quantity', 'price', 'prices'];

    protected $casts = [
        'prices' => 'array',
    ];

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

    public function getAmountProduct(Customer $customer): int
    {
        /** @var Wallet $wallet */
        $wallet = app(CastService::class)->getWallet($customer);

        if (array_key_exists($wallet->currency, $this->prices)) {
            return $this->prices[$wallet->currency];
        }

        throw new PriceNotSetException("Price not set for {$wallet->currency} currency");
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }
}
