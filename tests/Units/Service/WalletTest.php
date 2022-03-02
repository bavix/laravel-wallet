<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Services\WalletServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class WalletTest extends TestCase
{
    public function testFindBy(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $uuidFactoryService = app(UuidFactoryServiceInterface::class);
        $walletService = app(WalletServiceInterface::class);

        $uuid = $uuidFactoryService->uuid4();

        self::assertNull($walletService->findBySlug($buyer, 'default'));
        self::assertNull($walletService->findByUuid($uuid));
        self::assertNull($walletService->findById(-1));

        $buyer->wallet->uuid = $uuid; // @hack
        $buyer->deposit(100);

        self::assertNotNull($walletService->findBySlug($buyer, 'default'));
        self::assertNotNull($walletService->findByUuid($uuid));
        self::assertNotNull($walletService->findById((int) $buyer->wallet->getKey()));
    }

    public function testGetBySlug(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $walletService = app(WalletServiceInterface::class);

        $walletService->getBySlug($buyer, 'default');
    }

    public function testGetById(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);

        app(WalletServiceInterface::class)->getById(-1);
    }

    public function testGetByUuid(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);

        $uuidFactoryService = app(UuidFactoryServiceInterface::class);

        app(WalletServiceInterface::class)->getByUuid($uuidFactoryService->uuid4());
    }
}
