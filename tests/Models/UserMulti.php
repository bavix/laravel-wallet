<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 *
 * @package Bavix\Wallet\Test\Models
 * @property string $name
 * @property string $email
 */
class UserMulti extends Model implements Wallet
{
    use HasWallet, HasWallets;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'users';
    }
}
