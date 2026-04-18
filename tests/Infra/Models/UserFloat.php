<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
#[\Illuminate\Database\Eloquent\Attributes\Fillable(['name', 'email'])]
final class UserFloat extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;

    #[\Override]
    public function getTable(): string
    {
        return 'users';
    }
}
