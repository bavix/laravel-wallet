<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserFloat
 *
 * @package Bavix\Wallet\Test\Models
 * @property string $name
 * @property string $email
 */
class UserFloat extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;

    /**
     * @var array
     */
    protected $fillable = ['name', 'email'];

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'users';
    }
}
