<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Traits\CanPay;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 */
final class Buyer extends Model implements Customer
{
    use CanPay;

    public function getTable(): string
    {
        return 'users';
    }
}
