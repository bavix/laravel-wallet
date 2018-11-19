<?php

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;

class Wallet extends Model implements WalletInterface, WalletFloat
{

    use HasWalletFloat;

    /**
     * @var array
     */
    protected $fillable = [
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
