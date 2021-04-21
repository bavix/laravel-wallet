<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User.
 *
 * @property string $name
 * @property string $email
 */
class User extends Model implements Wallet
{
    use HasWallet;

    /**
     * @var array
     */
    protected $fillable = ['name', 'email'];
}
