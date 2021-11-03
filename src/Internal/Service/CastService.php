<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

/** @psalm-internal */
class CastService
{
    public function getWallet(Wallet $object, bool $save = true): WalletModel
    {
        assert($object instanceof Model);

        /** @var WalletModel $wallet */
        $wallet = $object;

        if (!($object instanceof WalletModel)) {
            $wallet = $object->getAttribute('wallet');
        }

        if ($save) {
            $wallet->exists or $wallet->save();
        }

        return $wallet;
    }

    /** @param Model|Wallet $object */
    public function getHolder($object): Model
    {
        assert($object instanceof Model);
        if ($object instanceof WalletModel) {
            assert($object->holder instanceof Model);

            return $object->holder;
        }

        return $object;
    }
}
