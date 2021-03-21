<?php

namespace Bavix\Wallet\Models;

use function app;
use function array_key_exists;
use function array_merge;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Exchangeable;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\CanExchange;
use Bavix\Wallet\Traits\CanPayFloat;
use Bavix\Wallet\Traits\HasGift;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Class Wallet.
 * @property string $holder_type
 * @property int $holder_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property array $meta
 * @property int $balance
 * @property int $decimal_places
 * @property \Bavix\Wallet\Interfaces\Wallet $holder
 * @property-read string $currency
 */
class Wallet extends Model implements Customer, WalletFloat, Confirmable, Exchangeable
{
    use CanConfirm;
    use CanExchange;
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
        'meta',
        'balance',
        'decimal_places',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    /**
     * {@inheritdoc}
     */
    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            config('wallet.wallet.casts', [])
        );
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (! $this->table) {
            $this->table = config('wallet.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist
         *  or the slug is empty.
         */
        if (! $this->exists && ! array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    /**
     * Under ideal conditions, you will never need a method.
     * Needed to deal with out-of-sync.
     *
     * @return bool
     */
    public function refreshBalance(): bool
    {
        return app(WalletService::class)->refresh($this);
    }

    /**
     * The method adjusts the balance by adding an additional transaction.
     * Used wisely, it can lead to serious problems.
     *
     * @return bool
     */
    public function adjustmentBalance(): bool
    {
        try {
            app(WalletService::class)->adjustment($this);

            return true;
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @return float|int
     */
    public function getAvailableBalance()
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

    /**
     * @return string
     */
    public function getCurrencyAttribute(): string
    {
        $currencies = config('wallet.currencies', []);

        return $currencies[$this->slug] ??
            $this->meta['currency'] ??
            Str::upper($this->slug);
    }
}
