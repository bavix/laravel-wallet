<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\BasketInterface;
use Bavix\Wallet\Dto\BasketDto;

class BasketService implements BasketInterface
{
    public function payCart(BasketDto $basketDto, bool $force = false): array
    {
        // TODO: Implement payCart() method.
    }

    public function safePayCart(BasketDto $basketDto, bool $force = false): array
    {
        // TODO: Implement safePayCart() method.
    }

    public function forcePayCart(BasketDto $basketDto): array
    {
        // TODO: Implement forcePayCart() method.
    }

    public function refundCart(BasketDto $basketDto, bool $force = false, bool $gifts = false): bool
    {
        // TODO: Implement refundCart() method.
    }

    public function safeRefundCart(BasketDto $basketDto, bool $force = false, bool $gifts = false): bool
    {
        // TODO: Implement safeRefundCart() method.
    }

    public function forceRefundCart(BasketDto $basketDto, bool $gifts = false): bool
    {
        // TODO: Implement forceRefundCart() method.
    }
}
