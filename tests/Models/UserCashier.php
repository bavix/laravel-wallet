<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Traits\MorphOneWallet;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

/**
 * Class User
 *
 * @package Bavix\Wallet\Test\Models
 * @property string $name
 * @property string $email
 */
class UserCashier extends Model
{
    use Billable, HasWallets, MorphOneWallet;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'users';
    }
}
