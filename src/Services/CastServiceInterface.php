<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\External\Contracts\CostDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

interface CastServiceInterface
{
    public function getCost(CostDtoInterface|float|int|string $dto): CostDtoInterface;

    public function getWallet(Wallet $object, bool $save = true): WalletModel;

    public function getHolder(Model|Wallet $object): Model;

    public function getModel(object $object): Model;
}
