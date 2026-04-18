<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Repository\PurchaseRepositoryInterface;
use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Services\TransferService;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class TransferServiceTest extends TestCase
{
    public function testUpdateStatusByIdsReturnsFalseForEmptyIds(): void
    {
        $transferRepository = $this->createMock(TransferRepositoryInterface::class);
        $transferRepository
            ->expects(self::never())
            ->method('updateStatusByIds');

        $purchaseRepository = $this->createMock(PurchaseRepositoryInterface::class);
        $purchaseRepository
            ->expects(self::never())
            ->method('updateStatusByTransferIds');

        $service = $this->makeService($purchaseRepository, $transferRepository);

        self::assertFalse($service->updateStatusByIds(TransferStatus::Refund, []));
    }

    public function testUpdateStatusByIdsReturnsFalseWhenTransferCountMismatch(): void
    {
        $transferRepository = $this->createMock(TransferRepositoryInterface::class);
        $transferRepository
            ->expects(self::once())
            ->method('updateStatusByIds')
            ->with(TransferStatus::Refund, [1, 2])
            ->willReturn(1);

        $purchaseRepository = $this->createMock(PurchaseRepositoryInterface::class);
        $purchaseRepository
            ->expects(self::never())
            ->method('updateStatusByTransferIds');

        $service = $this->makeService($purchaseRepository, $transferRepository);

        self::assertFalse($service->updateStatusByIds(TransferStatus::Refund, [1, 2]));
    }

    private function makeService(
        PurchaseRepositoryInterface $purchaseRepository,
        TransferRepositoryInterface $transferRepository
    ): TransferService {
        return new TransferService(
            $this->createMock(TransferDtoAssemblerInterface::class),
            $purchaseRepository,
            $transferRepository,
            $this->createMock(TransactionServiceInterface::class),
            $this->createMock(DatabaseServiceInterface::class),
            $this->createMock(CastServiceInterface::class),
            $this->createMock(AtmServiceInterface::class),
        );
    }
}
