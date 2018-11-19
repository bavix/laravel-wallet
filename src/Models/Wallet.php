<?php

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\CanBePaidFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Wallet
 * @package Bavix\Wallet\Models
 * @property int $balance
 */
class Wallet extends Model implements Customer, WalletFloat
{

    use CanBePaidFloat;

    /**
     * @var array
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'description',
        'balance',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'balance' => 'int',
    ];

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = \config('wallet.wallet.table');
        }

        return parent::getTable();
    }

    /**
     * @return bool
     */
    public function calculateBalance(): bool
    {
        $this->attributes['balance'] = $this->transactions()
            ->where('wallet_id', $this->getKey())
            ->where('confirmed', true)
            ->sum('amount');

        return $this->save();
    }

}
