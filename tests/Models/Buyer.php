<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Contracts\CustomerInterface;
use Bavix\Wallet\Traits\CanPay;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User.
 *
 * @property string $name
 * @property string $email
 */
class Buyer extends Model implements CustomerInterface
{
    use CanPay;

    public function getTable(): string
    {
        return 'users';
    }
}
