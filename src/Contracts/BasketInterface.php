<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Dto\BasketDto;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;

interface BasketInterface
{
    /** @return Transfer[] */
    public function payCart(Wallet $wallet, BasketDto $basketDto, bool $force = false): array;

    /** @return Transfer[] */
    public function forcePayCart(Wallet $wallet, BasketDto $basketDto): array;

    public function refundCart(Wallet $wallet, BasketDto $basketDto, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(Wallet $wallet, BasketDto $basketDto, bool $gifts = false): bool;
}
