<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;

interface TransferLazyDtoAssemblerInterface
{
    /**
     * Create transfer lazy dto.
     *
     * @param Wallet $fromWallet The source wallet.
     * @param Wallet $toWallet The destination wallet.
     * @param int $discount The discount amount.
     * @param string $fee The fee amount.
     * @param TransactionDtoInterface $withdrawDto The withdrawal transaction DTO.
     * @param TransactionDtoInterface $depositDto The deposit transaction DTO.
     * @param string $status The transfer status.
     * @param string|null $uuid The transfer UUID.
     * @param array<mixed>|null $extra The extra data.
     * @return TransferLazyDtoInterface The transfer lazy DTO.
     */
    public function create(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $discount,
        string $fee,
        TransactionDtoInterface $withdrawDto,
        TransactionDtoInterface $depositDto,
        string $status,
        ?string $uuid,
        ?array $extra,
    ): TransferLazyDtoInterface;
}
