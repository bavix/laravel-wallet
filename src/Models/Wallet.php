<?php

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\CanPayFloat;
use Bavix\Wallet\Traits\HasGift;
use Bavix\Wallet\WalletProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Class Wallet
 * @package Bavix\Wallet\Models
 * @property string $slug
 * @property int $balance
 * @property \Bavix\Wallet\Interfaces\Wallet $holder
 */
class Wallet extends Model implements Customer, WalletFloat
{

    use CanPayFloat;
    use HasGift;

    /**
     * @var array
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'description',
        'balance',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'balance' => 'int',
    ];

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = \config('wallet.wallet.table');
        }

        return parent::getTable();
    }

    /**
     * @param string $name
     * @return void
     */
    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist
         *  or the slug is empty
         */
        if (!$this->exists && !\array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    /**
     * @return bool
     */
    public function calculateBalance(): bool
    {
        $balance = $this->getAvailableBalance();
        WalletProxy::set($this->getKey(), $balance);
        $this->attributes['balance'] = $balance;

        return $this->save();
    }

    /**
     * @return int
     */
    public function getAvailableBalance(): int
    {
        return $this->transactions()
            ->where('wallet_id', $this->getKey())
            ->where('confirmed', true)
            ->sum('amount');
    }

    /**
     * @return MorphTo
     */
    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

}
