<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;

/**
 * @api
 */
interface PrepareServiceInterface
{
    /**
     * Deposit the specified amount of money into the wallet.
     *
     * @param float|int|string $amount The amount to deposit.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @param null|string $uuid The UUID of the transaction.
     * @return TransactionDtoInterface The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function deposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface;

    /**
     * Withdraw the specified amount of money from the wallet.
     *
     * @param float|int|string $amount The amount to withdraw.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @param null|string $uuid The UUID of the transaction.
     * @return TransactionDtoInterface The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function withdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface;

    /**
     * Transfer funds from one wallet to another.
     *
     * This function is a shortcut for the transferExtraLazy method.
     * It does not allow to specify the wallets models explicitly.
     *
     * @param Wallet $from The wallet from which funds are transferred.
     * @param Wallet $to The wallet to which funds are transferred.
     * @param string $status The status of the transfer.
     * @param float|int|string $amount The amount of funds to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transfer.
     * @return TransferLazyDtoInterface The transfer DTO.
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function transferLazy(
        Wallet $from,
        Wallet $to,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface;

    /**
     * Transfer funds from one wallet to another with extra data.
     *
     * @param Wallet $from The wallet from which funds are transferred.
     * @param WalletModel $fromWallet The model of the wallet from which funds are transferred.
     * @param Wallet $to The wallet to which funds are transferred.
     * @param WalletModel $toWallet The model of the wallet to which funds are transferred.
     * @param string $status The status of the transfer.
     * @param float|int|string $amount The amount of funds to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transfer.
     * @return TransferLazyDtoInterface The transfer lazy DTO.
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function transferExtraLazy(
        Wallet $from,
        WalletModel $fromWallet,
        Wallet $to,
        WalletModel $toWallet,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface;
}
