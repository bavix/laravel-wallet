<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\UuidFactoryService;

/** @deprecated  */
class Bring
{
    protected string $status;

    protected Wallet $from;

    protected Wallet $to;

    protected Transaction $deposit;

    protected Transaction $withdraw;

    protected string $uuid;

    protected ?int $fee = null;

    protected int $discount = 0;

    private MathInterface $mathService;

    public function __construct(
        MathInterface $mathService,
        UuidFactoryService $uuidFactoryService
    ) {
        $this->mathService = $mathService;
        $this->uuid = $uuidFactoryService->uuid4();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = (int) $this->mathService->round($discount);

        return $this;
    }

    public function getFrom(): Wallet
    {
        return $this->from;
    }

    /**
     * @return static
     */
    public function setFrom(Wallet $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): Wallet
    {
        return $this->to;
    }

    /**
     * @return static
     */
    public function setTo(Wallet $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getDeposit(): Transaction
    {
        return $this->deposit;
    }

    /**
     * @return static
     */
    public function setDeposit(Transaction $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getWithdraw(): Transaction
    {
        return $this->withdraw;
    }

    /**
     * @return static
     */
    public function setWithdraw(Transaction $withdraw): self
    {
        $this->withdraw = $withdraw;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getFee(): int
    {
        $fee = $this->fee;
        if ($fee === null) {
            $fee = (int) app(Mathable::class)->round(
                app(Mathable::class)->sub(
                    app(Mathable::class)->abs($this->getWithdraw()->amount),
                    app(Mathable::class)->abs($this->getDeposit()->amount)
                )
            );
        }

        return $fee;
    }

    /**
     * @param float|int|string $fee
     *
     * @return Bring
     */
    public function setFee($fee): self
    {
        $this->fee = (int) app(Mathable::class)->round($fee);

        return $this;
    }

    public function create(): Transfer
    {
        return app(Transfer::class)
            ->create($this->toArray())
        ;
    }

    public function toArray(): array
    {
        /** @var WalletModel $from */
        $from = $this->getFrom();
        /** @var WalletModel $to */
        $to = $this->getTo();

        return [
            'status' => $this->getStatus(),
            'deposit_id' => $this->getDeposit()->getKey(),
            'withdraw_id' => $this->getWithdraw()->getKey(),
            'from_type' => $from->getMorphClass(),
            'from_id' => $from->getKey(),
            'to_type' => $to->getMorphClass(),
            'to_id' => $to->getKey(),
            'discount' => $this->getDiscount(),
            'fee' => $this->getFee(),
            'uuid' => $this->getUuid(),
        ];
    }
}
