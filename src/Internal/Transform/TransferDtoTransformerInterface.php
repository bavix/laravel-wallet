<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Transform;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use DateTimeImmutable;

interface TransferDtoTransformerInterface
{
    /**
     * @return array{
     *     uuid: string,
     *     deposit_id: int,
     *     withdraw_id: int,
     *     status: string,
     *     from_id: int|string,
     *     to_id: int|string,
     *     discount: int,
     *     fee: string,
     *     extra: array<mixed>|null,
     *     created_at: DateTimeImmutable,
     *     updated_at: DateTimeImmutable,
     * }
     */
    public function extract(TransferDtoInterface $dto): array;
}
