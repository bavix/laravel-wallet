<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Contracts\WalletInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;

interface TransferLazyDtoAssemblerInterface
{
    public function create(
        WalletInterface $fromWallet,
        WalletInterface $toWallet,
        int $discount,
        string $fee,
        TransactionDto $withdrawDto,
        TransactionDto $depositDto,
        string $status
    ): TransferLazyDto;
}
