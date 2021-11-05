<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transaction;

/** @deprecated There is no alternative yet, but the class will be removed */
class Bring
{
    private string $status;

    private Wallet $from;

    private Wallet $to;

    private Transaction $deposit;

    private Transaction $withdraw;

    private ?string $fee = null;

    private string $discount;

    private MathInterface $math;

    public function __construct(MathInterface $math)
    {
        $this->math = $math;
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
        $this->discount = $this->math->round($discount);

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
        return (int) $this->discount;
    }

    public function getFee(): int
    {
        $fee = $this->fee;
        if ($fee === null) {
            $fee = $this->math->round(
                $this->math->sub(
                    $this->math->abs($this->getWithdraw()->amount),
                    $this->math->abs($this->getDeposit()->amount)
                )
            );
        }

        return (int) $fee;
    }

    /**
     * @param int $fee
     *
     * @return Bring
     */
    public function setFee($fee): self
    {
        $this->fee = $this->math->round($fee);

        return $this;
    }
}
