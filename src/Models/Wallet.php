<?php

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\CanBePaid;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Wallet
 * @package Bavix\Wallet\Models
 * @property int $balance
 */
class Wallet extends Model implements Customer, WalletFloat
{

    use HasWalletFloat, CanBePaid {
        CanBePaid::checkAmount insteadof HasWalletFloat;
        CanBePaid::deposit insteadof HasWalletFloat;
        CanBePaid::withdraw insteadof HasWalletFloat;
        CanBePaid::canWithdraw insteadof HasWalletFloat;
        CanBePaid::forceWithdraw insteadof HasWalletFloat;
        CanBePaid::transfer insteadof HasWalletFloat;
        CanBePaid::safeTransfer insteadof HasWalletFloat;
        CanBePaid::forceTransfer insteadof HasWalletFloat;
        CanBePaid::assemble insteadof HasWalletFloat;
        CanBePaid::change insteadof HasWalletFloat;
        CanBePaid::transactions insteadof HasWalletFloat;
        CanBePaid::transfers insteadof HasWalletFloat;
        CanBePaid::wallets insteadof HasWalletFloat;
        CanBePaid::wallet insteadof HasWalletFloat;
        CanBePaid::getBalanceAttribute insteadof HasWalletFloat;
        CanBePaid::addBalance insteadof HasWalletFloat;
    }

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

}
