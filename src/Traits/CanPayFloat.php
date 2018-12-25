<?php

namespace Bavix\Wallet\Traits;

trait CanPayFloat
{

    use HasWalletFloat, CanPay {
        CanPay::checkAmount insteadof HasWalletFloat;
        CanPay::deposit insteadof HasWalletFloat;
        CanPay::withdraw insteadof HasWalletFloat;
        CanPay::canWithdraw insteadof HasWalletFloat;
        CanPay::forceWithdraw insteadof HasWalletFloat;
        CanPay::transfer insteadof HasWalletFloat;
        CanPay::safeTransfer insteadof HasWalletFloat;
        CanPay::forceTransfer insteadof HasWalletFloat;
        CanPay::assemble insteadof HasWalletFloat;
        CanPay::change insteadof HasWalletFloat;
        CanPay::transactions insteadof HasWalletFloat;
        CanPay::transfers insteadof HasWalletFloat;
        CanPay::wallet insteadof HasWalletFloat;
        CanPay::getBalanceAttribute insteadof HasWalletFloat;
        CanPay::addBalance insteadof HasWalletFloat;
    }

}
