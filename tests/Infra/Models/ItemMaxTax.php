<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MaximalTaxable;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 */
final class ItemMaxTax extends Model implements ProductLimitedInterface, MaximalTaxable
{
    use HasWallet;

    /** @var string[] */
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

    public function getAmountProduct(Customer $customer): int
    {
        /** @var Wallet $wallet */
        $wallet = app(CastService::class)->getWallet($customer);

        return $this->price + (int) $wallet->holder_id;
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }

    public function getFeePercent(): float
    {
        return 3;
    }

    public function getMaximalFee(): int
    {
        return 300;
    }
}
