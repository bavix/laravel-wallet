<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Traits\CanPay;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 *
 * @package Bavix\Wallet\Test\Models
 * @property string $name
 * @property string $email
 */
class Buyer extends Model implements Customer
{
    use CanPay;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'users';
    }
}
