<?php

namespace Bavix\Wallet\Traits;

trait CanPayFloat
{
    use HasWalletFloat, CanPay {
        CanPay::deposit insteadof HasWalletFloat;
        CanPay::withdraw insteadof HasWalletFloat;
        CanPay::canWithdraw insteadof HasWalletFloat;
        CanPay::forceWithdraw insteadof HasWalletFloat;
        CanPay::transfer insteadof HasWalletFloat;
        CanPay::safeTransfer insteadof HasWalletFloat;
        CanPay::forceTransfer insteadof HasWalletFloat;
        CanPay::transactions insteadof HasWalletFloat;
        CanPay::transfers insteadof HasWalletFloat;
        CanPay::wallet insteadof HasWalletFloat;
        CanPay::getBalanceAttribute insteadof HasWalletFloat;
    }
}
