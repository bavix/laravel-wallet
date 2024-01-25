<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\FormatterServiceInterface;

final readonly class TransactionFloatQuery implements TransactionQueryInterface
{
    private string $amount;

    /**
     * @param self::TYPE_DEPOSIT|self::TYPE_WITHDRAW $type
     * @param array<mixed>|null $meta
     */
    private function __construct(
        private string $type,
        private Wallet $wallet,
        float|int|string $amount,
        private ?array $meta,
        private bool $confirmed,
        private ?string $uuid
    ) {
        $walletModel = app(CastServiceInterface::class)->getWallet($wallet);

        $this->amount = app(FormatterServiceInterface::class)
            ->intValue($amount, $walletModel->decimal_places);
    }

    /**
     * @param array<mixed>|null $meta
     */
    public static function createDeposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): self {
        return new self(self::TYPE_DEPOSIT, $wallet, $amount, $meta, $confirmed, $uuid);
    }

    /**
     * @param array<mixed>|null $meta
     */
    public static function createWithdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): self {
        return new self(self::TYPE_WITHDRAW, $wallet, $amount, $meta, $confirmed, $uuid);
    }

    /**
     * @return self::TYPE_DEPOSIT|self::TYPE_WITHDRAW
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @return array<mixed>|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
