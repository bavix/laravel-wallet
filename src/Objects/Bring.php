<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Ramsey\Uuid\Uuid;
use function abs;

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
     * @var int
     */
    protected $fee;

    /**
     * @var int
     */
    protected $discount;

    /**
     * Bring constructor.
     * @throws
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return static
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $discount
     * @return static
     */
    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getFrom(): Wallet
    {
        return $this->from;
    }

    /**
     * @param Wallet $from
     * @return static
     */
    public function setFrom(Wallet $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getTo(): Wallet
    {
        return $this->to;
    }

    /**
     * @param Wallet $to
     * @return static
     */
    public function setTo(Wallet $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return Transaction
     */
    public function getDeposit(): Transaction
    {
        return $this->deposit;
    }

    /**
     * @param Transaction $deposit
     * @return static
     */
    public function setDeposit(Transaction $deposit): self
    {
        $this->deposit = $deposit;
        return $this;
    }

    /**
     * @return Transaction
     */
    public function getWithdraw(): Transaction
    {
        return $this->withdraw;
    }

    /**
     * @param Transaction $withdraw
     * @return static
     */
    public function setWithdraw(Transaction $withdraw): self
    {
        $this->withdraw = $withdraw;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getDiscount(): int
    {
        return $this->discount;
    }

    /**
     * @return int
     */
    public function getFee(): int
    {
        if ($this->fee === null) {
            return abs($this->getWithdraw()->amount) - abs($this->getDeposit()->amount);
        }

        return $this->fee;
    }

    /**
     * @param int $fee
     * @return Bring
     */
    public function setFee(int $fee): self
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return Transfer
     * @throws
     */
    public function create(): Transfer
    {
        return app(Transfer::class)
            ->create($this->toArray());
    }

    /**
     * @return array
     * @throws
     */
    public function toArray(): array
    {
        return [
            'status' => $this->getStatus(),
            'deposit_id' => $this->getDeposit()->getKey(),
            'withdraw_id' => $this->getWithdraw()->getKey(),
            'from_type' => $this->getFrom()->getMorphClass(),
            'from_id' => $this->getFrom()->getKey(),
            'to_type' => $this->getTo()->getMorphClass(),
            'to_id' => $this->getTo()->getKey(),
            'discount' => $this->getDiscount(),
            'fee' => $this->getFee(),
            'uuid' => $this->getUuid(),
        ];
    }

}
