<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MerchantFeeDeductible;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 *
 * @method int getKey()
 */
final class ItemMerchantFeeDeductible extends Model implements ProductLimitedInterface, MerchantFeeDeductible
{
    use HasWallet;

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'quantity', 'price'];

    #[\Override]
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

        return $result && ! $customer->paid($this) instanceof \Bavix\Wallet\Models\Transfer;
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

    /**
     * Specify the percentage of the amount. For example, the product costs $100, the fee is 5%.
     * With MerchantFeeDeductible, customer pays $100, merchant receives $95.
     *
     * Minimum 0; Maximum 100 Example: return 5.0; // 5%
     */
    public function getFeePercent(): float
    {
        return 5.0;
    }
}
