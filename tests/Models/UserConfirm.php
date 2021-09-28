<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserConfirm.
 *
 * @property string $name
 * @property string $email
 */
class UserConfirm extends Model implements Wallet, Confirmable
{
    use HasWallet;
    use CanConfirm;

    /**
     * @var array
     */
    protected $fillable = ['name', 'email'];

    public function getTable(): string
    {
        return 'users';
    }
}
