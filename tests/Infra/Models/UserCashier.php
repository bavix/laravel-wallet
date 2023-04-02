<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Traits\MorphOneWallet;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class UserCashier extends Model
{
    use Billable;
    use HasWallets;
    use MorphOneWallet;

    public function getTable(): string
    {
        return 'users';
    }
}
