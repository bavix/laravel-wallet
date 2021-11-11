<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Models\Transaction;

/** @deprecated There is no alternative yet, but the class will be removed */
class Bring
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var Wallet
     */
    protected $from;

    /**
     * @var Wallet
     */
    protected $to;

    /**
     * @var Transaction
     */
    protected $deposit;

    /**
     * @var Transaction
     */
    protected $withdraw;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var null|int
     */
    protected $fee;

    /**
     * @var int
     */
    protected $discount;

    public function __construct(UuidInterface $uuidService)
    {
        $this->uuid = $uuidService->uuid4();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return static
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return static
     */
    public function setDiscount(int $discount): self
    {
        $this->discount = app(MathInterface::class)->round($discount);

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

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getFee(): int
    {
        $fee = $this->fee;
        if ($fee === null) {
            $fee = app(MathInterface::class)->round(
                app(MathInterface::class)->sub(
                    app(MathInterface::class)->abs($this->getWithdraw()->amount),
                    app(MathInterface::class)->abs($this->getDeposit()->amount)
                )
            );
        }

        return $fee;
    }

    /**
     * @param int $fee
     *
     * @return Bring
     */
    public function setFee($fee): self
    {
        $this->fee = app(MathInterface::class)->round($fee);

        return $this;
    }
}
