<?php

namespace Bavix\Wallet\Traits;

trait CanBePaidFloat
{

    use HasWalletFloat, CanBePaid {
        CanBePaid::checkAmount insteadof HasWalletFloat;
        CanBePaid::deposit insteadof HasWalletFloat;
        CanBePaid::withdraw insteadof HasWalletFloat;
        CanBePaid::canWithdraw insteadof HasWalletFloat;
        CanBePaid::forceWithdraw insteadof HasWalletFloat;
        CanBePaid::transfer insteadof HasWalletFloat;
        CanBePaid::safeTransfer insteadof HasWalletFloat;
        CanBePaid::forceTransfer insteadof HasWalletFloat;
        CanBePaid::assemble insteadof HasWalletFloat;
        CanBePaid::change insteadof HasWalletFloat;
        CanBePaid::transactions insteadof HasWalletFloat;
        CanBePaid::transfers insteadof HasWalletFloat;
        CanBePaid::wallets insteadof HasWalletFloat;
        CanBePaid::wallet insteadof HasWalletFloat;
        CanBePaid::getBalanceAttribute insteadof HasWalletFloat;
        CanBePaid::addBalance insteadof HasWalletFloat;
    }

}
