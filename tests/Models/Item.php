<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class Item.
 *
 * @property string $name
 * @property int    $quantity
 * @property int    $price
 */
class Item extends Model implements Product
{
    use HasWallet;

    protected $fillable = ['name', 'quantity', 'price'];

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        $result = $this->quantity >= $quantity;

        if ($force) {
            return $result;
        }

        return $result && !$customer->paid($this);
    }

    public function getAmountProduct(Customer $customer)
    {
        /** @var Wallet $wallet */
        $wallet = app(CastService::class)->getWallet($customer);

        return $this->price + $wallet->holder_id;
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }

    /**
     * @param int[] $walletIds
     */
    public function boughtGoods(array $walletIds): MorphMany
    {
        return $this
            ->morphMany(config('wallet.transfer.model', Transfer::class), 'to')
            ->where('status', Transfer::STATUS_PAID)
            ->where('from_type', config('wallet.wallet.model', Wallet::class))
            ->whereIn('from_id', $walletIds)
        ;
    }
}
