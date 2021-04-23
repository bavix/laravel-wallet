<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\BasketInterface;
use Bavix\Wallet\Dto\BasketDto;
use Bavix\Wallet\Models\Wallet;

class BasketService implements BasketInterface
{
    public function payCart(Wallet $wallet, BasketDto $basketDto, bool $force = false): array
    {
        // TODO: Implement payCart() method.
    }

    public function forcePayCart(Wallet $wallet, BasketDto $basketDto): array
    {
        // 1. multi transactions & get models
        // 2. multi transfers & get models
        // 3. update wallet.balance & store.balance
        // 4. return transfers
    }

    public function refundCart(Wallet $wallet, BasketDto $basketDto, bool $force = false, bool $gifts = false): bool
    {
        // TODO: Implement refundCart() method.
    }

    public function forceRefundCart(Wallet $wallet, BasketDto $basketDto, bool $gifts = false): bool
    {
        // TODO: Implement forceRefundCart() method.
    }
}
